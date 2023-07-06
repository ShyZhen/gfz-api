<?php
/**
 * 基础数据加密服务,实现数据的aes加解密
 * 可以用在用户关键信息的加密存储，解密查询
 *
 * @Author huaixiu.zhen@gmail.com
 * http://litblc.com
 * User: huaixiu.zhen
 * Date: 2023/7/5
 * Time: 15:05
 */

namespace App\Services\BaseService;

use App\Services\Service;

class CryptService extends Service
{
    const ENCRYPT_KEY = 'FMOCKCRYPTKEY';

    public static function encrypt($data)
    {
        return  self::ENCRYPT_KEY ? strtoupper(bin2hex(base64_decode(openssl_encrypt($data, 'aes-128-ecb', self::ENCRYPT_KEY)))) : $data;
    }

    public static function decrypt($data)
    {
        return self::ENCRYPT_KEY ? openssl_decrypt(base64_encode(hex2bin(strtolower($data))), 'aes-128-ecb', self::ENCRYPT_KEY) : $data;
    }
}
