<?php
require_once 'autoload.php';

use OSS\OssClient;
use OSS\Core\OssException;
use OSS\Http\RequestCore;
use OSS\Http\ResponseCore;

$accessKeyId = "LTAI3LMWItx7oEXl";
$accessKeySecret = "h5MBb2E72dNBPrOTMiPuirg9Rxeze4";
$endpoint = "http://oss-cn-shenzhen.aliyuncs.com";
$bucket= "hbtech2";
$object = "1/20190617200155_GcdkEpok_Taylor Swift - Red.mp3";

// 设置URL的有效期为600秒。
$timeout = 600;
try {
    $ossClient = new OssClient($accessKeyId, $accessKeySecret, $endpoint, false);

    // 生成GetObject的签名URL。
    $signedUrl = $ossClient->signUrl($bucket, $object, $timeout);
} catch (OssException $e) {
    $ossUrl = __FUNCTION__ . ": FAILED\n";
    $ossUrl = $e->getMessage() . "\n";
    return;
}

$ossUrl = __FUNCTION__ . $signedUrl;

echo $ossUrl;

?>