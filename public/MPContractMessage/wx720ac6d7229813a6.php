<?php

require_once './config.php';

define('TOKEN', MP1TOKEN);
define('APPID', MP1APPID);
define('APPSECRET', MP1APPSECRET);
define('IMAGEURL', MP1IMAGEURL);

if (isset($_GET['echostr'])) {
    valid();
} else {
    responseMsg();
}

function valid()
{
    $echoStr = $_GET['echostr'];
    if (checkSignature()) {
        header('content-type:text');
        echo $echoStr;
        exit;
    } else {
        echo $echoStr.'+++'.TOKEN;
        exit;
    }
}

function checkSignature()
{
    $signature = $_GET['signature'];
    $timestamp = $_GET['timestamp'];
    $nonce = $_GET['nonce'];

    $token = TOKEN;
    $tmpArr = [$token, $timestamp, $nonce];
    sort($tmpArr, SORT_STRING);
    $tmpStr = implode($tmpArr);
    $tmpStr = sha1($tmpStr);

    if ($tmpStr == $signature) {
        return true;
    } else {
        return false;
    }
}

function responseMsg()
{
    $postStr = file_get_contents('php://input');   //此处推荐使用file_get_contents('php://input')获取后台post过来的数据

    if (!empty($postStr) && is_string($postStr)) {
        $postArr = json_decode($postStr,true);
        if (!empty($postArr['MsgType']) && $postArr['Content'] == '1') {    // 用户发送1

            //发送者openid
            $fromUsername = $postArr['FromUserName'];
            $content = "您好:\n为了提高服务质量，请添加小助手为您执行兑换服务\n微信号: wangzhexingyunxing \n（小助手不会索要您任何个人信息）";
            $data=array(
                'touser' => $fromUsername,
                'msgtype' => 'text',
                'text' => ['content' => $content]
            );
            $json = json_encode($data,JSON_UNESCAPED_UNICODE);
            requestAPI($json);


            $imgurl = IMAGEURL;
            $media_id = getMediaId($imgurl);
            $data = [
                'touser' => $fromUsername,
                'msgtype' => 'image',
                'image' => ['media_id' => $media_id]
            ];
            $json = json_encode($data,JSON_UNESCAPED_UNICODE);
            requestAPI($json);
            exit;
        }

        elseif (!empty($postArr['MsgType']) && $postArr['Content'] == '2') {
            $fromUsername = $postArr['FromUserName'];
            $imgurl = IMAGEURL;
            $media_id = getMediaId($imgurl);
            $data = [
                'touser' => $fromUsername,
                'msgtype'=>'image',
                'image' => ['media_id' => $media_id]
            ];
            $json = json_encode($data,JSON_UNESCAPED_UNICODE);
            requestAPI($json);
            exit;
        }

        elseif (!empty($postArr['MsgType']) && $postArr['Content'] == '3') {
            $fromUsername = $postArr['FromUserName'];
            $data=array(
                'touser' => $fromUsername,
                'msgtype' => 'link',
                'link' => [
                    'title' => '头像挂件工坊',
                    'description' => '给您的头像，加上精美的节日挂件',
                    'url' => 'https://mp.weixin.qq.com/s/JFBbjMttOWXf7VX-R26JSg',
                    'thumb_url' => 'https://image.fmock.com/mp2-logo.jpg',
                ]
            );
            $json = json_encode($data,JSON_UNESCAPED_UNICODE);
            requestAPI($json);
            exit;
        }

        // 用户进入客服后马上回复，现在已失效,需要用户先发过消息
        elseif ($postArr['MsgType'] == 'event' && $postArr['Event']=='user_enter_tempsession') {
            $fromUsername = $postArr['FromUserName'];
            $content = "您好:\n\n回复 1 兑换奖品\n\n回复 2 加小助手进行人工服务\n\n回复 3 获取精美头像挂件；\n";
            $data = [
                'touser' => $fromUsername,
                'msgtype' => 'text',
                'text' => ['content' => $content]
            ];
            $json = json_encode($data,JSON_UNESCAPED_UNICODE);
            requestAPI($json);
            exit;
        }

        /*
        elseif(!empty($postArr['MsgType']) && $postArr['Content'] == "3"){      //用户发送3,回复文字
            $fromUsername = $postArr['FromUserName'];                           //发送者openid
            $content = '好的，收到您的要求';                                    //修改为自己需要的文字
            $data=array(
                "touser"=>$fromUsername,
                "msgtype"=>"text",
                "text"=>array("content"=>$content)
            );
            $json = json_encode($data,JSON_UNESCAPED_UNICODE);
            requestAPI($json);
            exit;
        }


        elseif(!empty($postArr['MsgType']) && $postArr['MsgType'] == 'image'){  //用户发送图片,这里示例为回复他公众号二维码
            $fromUsername = $postArr['FromUserName'];                           //发送者openid
            $imgurl = "/300-300.png";                                           //公众号二维码,相对路径,修改为自己的
            $media_id = getMediaId($imgurl);                                    //获取图片消息的media_id
            $data=array(
                "touser"=>$fromUsername,
                "msgtype"=>"image",
                "image"=>array("media_id"=>$media_id)
            );
            $json = json_encode($data,JSON_UNESCAPED_UNICODE);
            requestAPI($json);
            exit;
        }

        elseif($postArr['MsgType'] !== 'event'){        //用户发送其他内容,引导加客服
            $fromUsername = $postArr['FromUserName'];   //发送者openid
            $imgurl = "/miniapp300-300.png";            //客服微信二维码,相对路径
            $media_id = getMediaId($imgurl);            //获取图片消息的media_id
            $data=array(
                "touser"=>$fromUsername,
                "msgtype"=>"image",
                "image"=>array("media_id"=>$media_id)
            );
            $json = json_encode($data,JSON_UNESCAPED_UNICODE);  //php5.4+
            requestAPI($json);
            exit;
        }
        */

        else {
            $fromUsername = $postArr['FromUserName'];
            $content = "您好:\n\n回复 1 兑换奖品\n\n回复 2 加小助手进行人工服务\n\n回复 3 获取精美头像挂件；\n";
            $data = [
                'touser' => $fromUsername,
                'msgtype' => 'text',
                'text' => ['content' => $content]
            ];
            $json = json_encode($data,JSON_UNESCAPED_UNICODE);
            requestAPI($json);
            exit;
        }

    } else {
        echo "empty";
        // print_r(APPID);
        exit;
    }
}

function requestAPI($json){
    $access_token = get_accessToken();
    $url = 'https://api.weixin.qq.com/cgi-bin/message/custom/send?access_token='.$access_token;

    $curl = curl_init();
    curl_setopt($curl, CURLOPT_URL, $url);
    curl_setopt($curl, CURLOPT_POST, 1);
    curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, FALSE);
    curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, FALSE);
    if (!empty($json)){
        curl_setopt($curl, CURLOPT_POSTFIELDS,$json);
    }
    curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
    //curl_setopt($curl, CURLOPT_HTTPHEADER, $headers );
    $output = curl_exec($curl);
    if (curl_errno($curl)) {
        echo "Errno".curl_error($curl);
    }
    curl_close($curl);
    if($output == 0){
        ob_clean();
        echo "success";
    }
}
/* 调用微信api，获取access_token，有效期7200s*/
function get_accessToken(){
    $url = 'https://api.weixin.qq.com/cgi-bin/token?grant_type=client_credential&appid='.APPID.'&secret='.APPSECRET;
    @$weixin = file_get_contents($url);
    @$jsondecode = json_decode($weixin);
    @$array = get_object_vars($jsondecode);
    $token = $array['access_token'];
    return $token;
}

//获取上传图片的media_id
function getMediaId($imgurl){
    $token=get_accessToken();
    $url = "https://api.weixin.qq.com/cgi-bin/media/upload?access_token={$token}&type=image";
    // echo $url;
    $ch1 = curl_init();
    $timeout = 10;
    $real_path = "{$_SERVER['DOCUMENT_ROOT']}$imgurl";//自动转成图片文件绝对路径,如果图片发送失败,检查PHP的$_SERVER['DOCUMENT_ROOT'的配置
    // echo $real_path;
    $data = array("media" =>  new CURLFile("{$real_path}"));//php5.6(含)以上版本使用此方法
    // var_dump($data);
    curl_setopt($ch1, CURLOPT_URL, $url);
    curl_setopt($ch1, CURLOPT_POST, 1);
    curl_setopt($ch1, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($ch1, CURLOPT_CONNECTTIMEOUT, $timeout);
    curl_setopt($ch1, CURLOPT_SSL_VERIFYPEER, FALSE);
    curl_setopt($ch1, CURLOPT_SSL_VERIFYHOST, false);
    curl_setopt($ch1, CURLOPT_POSTFIELDS, $data);
    $result = curl_exec($ch1);
    // echo $result;
    curl_close($ch1);
    if($result){
        $result = json_decode($result, true);
        return $result['media_id'];
    } else{
        return null;
    }
}

?>
