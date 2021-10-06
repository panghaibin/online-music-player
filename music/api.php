<?php
error_reporting(E_ALL^E_NOTICE^E_WARNING);
require_once 'plugns/oss/autoload.php';

use OSS\OssClient;
use OSS\Core\OssException;

include "config.php";

/**************************************************
 * MKOnlinePlayer v3.0
 * 后台音乐数据抓取模块
 * 歌词合并，付费歌曲替换 by hbtech
 * 编写：mengkun(https://mkblog.cn) hbtech(https://hbte.ch)
 * 时间：mengkun 2018-3-11 / hbtech 2018-06-06
 * 特别感谢 @metowolf 提供的 Meting.php
 *************************************************/

/**
* cookie 获取及使用方法见 
* https://github.com/mengkunsoft/MKOnlineMusicPlayer/wiki/%E7%BD%91%E6%98%93%E4%BA%91%E9%9F%B3%E4%B9%90%E9%97%AE%E9%A2%98
* 
* 更多相关问题可以查阅项目 wiki 
* https://github.com/mengkunsoft/MKOnlineMusicPlayer/wiki
* 
* 如果还有问题，可以提交 issues
* https://github.com/mengkunsoft/MKOnlineMusicPlayer/issues
**/


define('HTTPS', false);    // 如果您的网站启用了https，请将此项置为“true”，如果你的网站未启用 https，建议将此项设置为“false”
define('DEBUG', false);      // 是否开启调试模式，正常使用时请将此项置为“false”
define('CACHE_PATH', 'cache/');     // 文件缓存目录,请确保该目录存在且有读写权限。如无需缓存，可将此行注释掉

/*
 如果遇到程序不能正常运行，请开启调试模式，然后访问 http://你的网站/音乐播放器地址/api.php ，进入服务器运行环境检测。
 此外，开启调试模式后，程序将输出详细的运行错误信息，方便定位错误原因。
 
 因为调试模式下程序会输出服务器环境信息，为了您的服务器安全，正常使用时请务必关闭调试。
*/

/*****************************************************************************************************/
if(!defined('DEBUG') || DEBUG !== true) error_reporting(0); // 屏蔽服务器错误

require_once('plugns/Meting.php');

use Metowolf\Meting;

$source = getParam('source', 'netease');  // 歌曲源
$API = new Meting($source);

$API->format(true); // 启用格式化功能

if($source == 'kugou' || $source == 'baidu') {
    define('NO_HTTPS', true);        // 酷狗和百度音乐源暂不支持 https
} elseif(($source == 'netease') && $netease_cookie) {
    $API->cookie($netease_cookie);    // 解决网易云 Cookie 失效
}

// 没有缓存文件夹则创建
if(defined('CACHE_PATH') && !is_dir(CACHE_PATH)) createFolders(CACHE_PATH);

$types = getParam('types');
switch($types)   // 根据请求的 Api，执行相应操作
{
    case 'url':   // 获取歌曲链接
        $id = getParam('id');  // 歌曲ID
        
        $data = $API->url($id);
        
        $get_data = handle_json($data);
        break;
        
    case 'pic':   // 获取歌曲链接
        $id = getParam('id');  // 歌曲ID
        
        $data = $API->pic($id);

        $get_data = handle_json($data);
        break;
    
    case 'lyric':       // 获取歌词
        $id = getParam('id');  // 歌曲ID
        
        if(($source == 'netease') && defined('CACHE_PATH')) {
            $cache = CACHE_PATH.$source.'_'.$types.'_'.$id.'.json';
            
            if(file_exists($cache)) {   // 缓存存在，则读取缓存
                $data = file_get_contents($cache);
            } else {
                $data = $API->lyric($id);
                
                // 只缓存链接获取成功的歌曲
                if(json_decode($data)->lyric !== '') {
                    file_put_contents($cache, $data);
                }
            }
        } else {
            $data = $API->lyric($id);
        }

        $get_data = handle_json($data);
        break;
        
    case 'download':    // 下载歌曲(弃用)
        $fileurl = getParam('url');  // 链接
        
        header('location:$fileurl');
        exit();
        break;
    
    case 'userlist':    // 获取用户歌单列表
        $uid = getParam('uid');  // 用户ID
        
        $url= 'http://music.163.com/api/user/playlist/?offset=0&limit=1001&uid='.$uid;
        $data = file_get_contents($url);

        $get_data = handle_json($data);
        break;
        
    case 'playlist':    // 获取歌单中的歌曲
        $id = getParam('id');  // 歌单ID
        
        if(($source == 'netease') && defined('CACHE_PATH')) {
            $cache = CACHE_PATH.$source.'_'.$types.'_'.$id.'.json';
            
            if(file_exists($cache) && (date("Ymd", filemtime($cache)) == date("Ymd"))) {   // 缓存存在，则读取缓存
                $data = file_get_contents($cache);
            } else {
                $data = $API->format(false)->playlist($id);
                
                // 只缓存链接获取成功的歌曲
                if(isset(json_decode($data)->playlist->tracks)) {
                    file_put_contents($cache, $data);
                }
            }
        } else {
            $data = $API->format(false)->playlist($id);
        }

        $get_data = handle_json($data);
        break;
     
    case 'search':  // 搜索歌曲
        $s = getParam('name');  // 歌名
        $limit = getParam('count', 20);  // 每页显示数量
        $pages = getParam('pages', 1);  // 页码
        
        $data = $API->search($s, [
            'page' => $pages, 
            'limit' => $limit
        ]);

        $get_data = handle_json($data);
        break;
        
    default:
        echo '<!doctype html><html><head><meta charset="utf-8"><title>信息</title><style>* {font-family: microsoft yahei}</style></head><body> <h2>MKOnlinePlayer</h2><h3>Github: https://github.com/mengkunsoft/MKOnlineMusicPlayer</h3><br>';
        if(!defined('DEBUG') || DEBUG !== true) {   // 非调试模式
            echo '<p>Api 调试模式已关闭</p>';
        } else {
            echo '<p><font color="red">您已开启 Api 调试功能，正常使用时请在 api.php 中关闭该选项！</font></p><br>';
            
            echo '<p>PHP 版本：'.phpversion().' （本程序要求 PHP 5.4+）</p><br>';
            
            echo '<p>服务器函数检查</p>';
            echo '<p>curl_exec: '.checkfunc('curl_exec',true).' （用于获取音乐数据）</p>';
            echo '<p>file_get_contents: '.checkfunc('file_get_contents',true).' （用于获取音乐数据）</p>';
            echo '<p>json_decode: '.checkfunc('json_decode',true).' （用于后台数据格式化）</p>';
            echo '<p>hex2bin: '.checkfunc('hex2bin',true).' （用于数据解析）</p>';
            echo '<p>openssl_encrypt: '.checkfunc('openssl_encrypt',true).' （用于数据解析）</p>';
        }
        
        echo '</body></html>';
}

/**
 * 创建多层文件夹 
 * @param $dir 路径
 */
function createFolders($dir) {
    return is_dir($dir) or (createFolders(dirname($dir)) and mkdir($dir, 0755));
}

/**
 * 检测服务器函数支持情况
 * @param $f 函数名
 * @param $m 是否为必须函数
 * @return 
 */
function checkfunc($f,$m = false) {
	if (function_exists($f)) {
		return '<font color="green">可用</font>';
	} else {
		if ($m == false) {
			return '<font color="black">不支持</font>';
		} else {
			return '<font color="red">不支持</font>';
		}
	}
}

/**
 * 获取GET或POST过来的参数
 * @param $key 键值
 * @param $default 默认值
 * @return 获取到的内容（没有则为默认值）
 */
function getParam($key, $default='')
{
    return trim($key && is_string($key) ? (isset($_POST[$key]) ? $_POST[$key] : (isset($_GET[$key]) ? $_GET[$key] : $default)) : $default);
}

/**
 * 输出一个json或jsonp格式的内容
 * @param $data 数组内容
 */
function handle_json($data)    //json和jsonp通用
{
    header('Content-type: application/json');
    
    if(defined('HTTPS') && HTTPS === true && !defined('NO_HTTPS')) {    // 替换链接为 https
        $data = str_replace('http:\/\/', 'https:\/\/', $data);
        $data = str_replace('http://', 'https://', $data);
    }

    return $data;
}

function post($url,$data){
    $curl = curl_init();
    curl_setopt($curl, CURLOPT_URL, $url); // 要访问的地址
    curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, 0); // 对认证证书来源的检查
    curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, 2); // 从证书中检查SSL加密算法是否存在
    curl_setopt($curl, CURLOPT_USERAGENT, $_SERVER['HTTP_USER_AGENT']); // 模拟用户使用的浏览器
    curl_setopt($curl, CURLOPT_FOLLOWLOCATION, 1); // 使用自动跳转
    curl_setopt($curl, CURLOPT_AUTOREFERER, 1); // 自动设置Referer
    curl_setopt($curl, CURLOPT_POST, 1); // 发送一个常规的Post请求
    curl_setopt($curl, CURLOPT_POSTFIELDS, $data); // Post提交的数据包
    curl_setopt($curl, CURLOPT_TIMEOUT, 30); // 设置超时限制防止死循环
    curl_setopt($curl, CURLOPT_HEADER, 0); // 显示返回的Header区域内容
    curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1); // 获取的信息以文件流的形式返回
    $tmpInfo = curl_exec($curl); // 执行操作
    if (curl_errno($curl)) {
        echo 'Error'.curl_error($curl);//捕抓异常
    }
    curl_close($curl); // 关闭CURL会话
    return $tmpInfo; // 返回数据，json格式
}

//**********歌词合并开始**********
$lyricInput = json_decode($get_data);

//对歌词进行正则匹配
preg_match_all("/(\[(\d+:\d+)(\.\d+)?\])(.*)/", $lyricInput->lyric, $lyricOutput->lyric);
preg_match_all("/(\[(\d+:\d+)(\.\d+)?\])(.*)/", $lyricInput->tlyric, $lyricOutput->tlyric);

//判断能不能匹配到翻译歌词，没有就直接输出了
if ($lyricOutput->tlyric[1]) {
    //计时开始，用于防止合并时出错程序卡死
    $t1 = microtime(true);
    //计算两种歌词的行数，--是因为计数从0开始
    $lyricLines = count($lyricOutput->lyric[0]);
    $lyricLines --;
    $tlyricLines = count($lyricOutput->tlyric[0]);
    $tlyricLines --;
    //计数器
    $iLyric = 0;
    $iTlyric = 0;

    //用来删除多余的空行
    function array_remove(&$arr, $offset) {
        array_splice($arr, $offset, 1);
    }
    //删除多余行
    if ($lyricLines != $tlyricLines) {
        while ($iLyric <= $lyricLines) {
            if (
                $lyricOutput->lyric[4][$iLyric] == "\n"
                or $lyricOutput->lyric[4][$iLyric] == ""
                or $lyricOutput->lyric[4][$iLyric] == " \n"
                or $lyricOutput->lyric[4][$iLyric] == " "
                or preg_match("/\](\ )+(\n)/", $lyricOutput->lyric[0][$iLyric])
            ) {
                array_remove($lyricOutput->lyric[0], $iLyric);
                array_remove($lyricOutput->lyric[1], $iLyric);
                array_remove($lyricOutput->lyric[2], $iLyric);
                array_remove($lyricOutput->lyric[3], $iLyric);
                array_remove($lyricOutput->lyric[4], $iLyric);
                $lyricLines --;
            }else{
                $iLyric ++;
            }
        }
        while ($iTlyric <= $tlyricLines) {
            if (
                $lyricOutput->tlyric[4][$iTlyric] == "\n"
                or $lyricOutput->tlyric[4][$iTlyric] == ""
                or $lyricOutput->tlyric[4][$iTlyric] == " \n"
                or $lyricOutput->tlyric[4][$iTlyric] == " "
                or preg_match("/\](\ )+/", $lyricOutput->tlyric[0][$iTlyric])
            ) {
                array_remove($lyricOutput->tlyric[0], $iTlyric);
                array_remove($lyricOutput->tlyric[1], $iTlyric);
                array_remove($lyricOutput->tlyric[2], $iTlyric);
                array_remove($lyricOutput->tlyric[3], $iTlyric);
                array_remove($lyricOutput->tlyric[4], $iTlyric);
                $tlyricLines --;
            }else{
                $iTlyric ++;
            }
        }
    }

    $iLyric = 0;
    $iTlyric = 0;

    $correctLyricTime = count(array_intersect($lyricOutput->lyric[1], $lyricOutput->tlyric[1]));
    if (($correctLyricTime / $lyricLines) >= 0.9) {
        $lyricTimeState = 1;
    }else {
        $lyricTimeState = 2;
    }

    if ($lyricOutput->tlyric[0][0]) {
        if ($lyricLines == $tlyricLines) {
            while ($iLyric <= $lyricLines or $iTlyric <= $tlyricLines) {
                $lyricOutput->mlyric[$iLyric] =  $lyricOutput->lyric[1][$iLyric] . $lyricOutput->lyric[4][$iLyric] . " <br> " . $lyricOutput->tlyric[4][$iTlyric] . "&nbsp;";
                $iLyric ++;
                $iTlyric ++;
            }
        }elseif ($lyricLines > $tlyricLines) {
            $t2 = microtime(true);
            while (($iLyric <= $lyricLines or $iTlyric <= $tlyricLines ) and (($t2-$t1)*1000) < 500) {
                $t2 = microtime(true);
                if ($lyricOutput->lyric[$lyricTimeState][$iLyric] == $lyricOutput->tlyric[$lyricTimeState][$iTlyric]) {
                    $lyricOutput->mlyric[$iLyric] =  $lyricOutput->lyric[1][$iLyric] . $lyricOutput->lyric[4][$iLyric] . " <br> " . $lyricOutput->tlyric[4][$iTlyric] . "&nbsp;";
                    $iLyric ++;
                    $iTlyric ++;
                }else {
                    $lyricOutput->mlyric[$iLyric] = $lyricOutput->lyric[1][$iLyric] . $lyricOutput->lyric[4][$iLyric] . " <br> &nbsp;";
                    $iLyric ++;
                }
            }
        }elseif ($lyricLines < $tlyricLines) {
            $t2 = microtime(true);
            while (($iLyric <= $lyricLines or $iTlyric <= $tlyricLines) and (($t2-$t1)*1000) < 500) {
                $t2 = microtime(true);
                if ($lyricOutput->lyric[$lyricTimeState][$iLyric] == $lyricOutput->tlyric[$lyricTimeState][$iTlyric]) {
                    $lyricOutput->mlyric[$iTlyric] =  $lyricOutput->lyric[1][$iLyric] . $lyricOutput->lyric[4][$iLyric] . " <br> " . $lyricOutput->tlyric[4][$iTlyric] . "&nbsp;";
                    $iLyric ++;
                    $iTlyric ++;
                }else {
                    $lyricOutput->mlyric[$iTlyric] = $lyricOutput->tlyric[1][$iTlyric] . $lyricOutput->tlyric[4][$iTlyric] . " <br> &nbsp;";
                    $iTlyric ++;
                }
            }
        }
    }

    if ((($t2-$t1)*1000) < 500) {
        $mlyricLines = count($lyricOutput->mlyric);
        $mlyricLines --;
        $iMlyric = 1;

        $mlyric = $lyricOutput->mlyric[0];

        while ($iMlyric <= $mlyricLines) {
            $mlyric = $mlyric . "\n" . $lyricOutput->mlyric[$iMlyric];
            $iMlyric ++;
        }

        $get_data = '';
        $get_data->lyric = $mlyric;
        $get_data->olyric = $lyricInput->lyric;
        $get_data->tlyric = $lyricInput->tlyric;
        $get_data = json_encode($get_data);
    }
    $get_data = str_replace("腾讯享有本翻译作品的著作权", "", $get_data);
    //$data = $_GET['callback'] . '(' . $data . ')';
}
//**********歌词合并结束**********

//**********替换部分网易云付费链接开始**********
if ($_POST['types'] == 'url' AND $_POST['source'] == 'netease') {

    $countUrl = count($urls);
    $i = 0;
    //echo $countUrl;
    while ($i < $countUrl) {
        if ($_POST['id'] == $urls[$i][0]) {
            //设置 object
            $object = $urls[$i][1];
            // 设置URL的有效期为1小时。
            $timeout = 1 * 60 * 60;
            try {
                $ossClient = new OssClient($accessKeyId, $accessKeySecret, $endpoint, false);
                // 生成GetObject的签名URL。
                $signedUrl = $ossClient->signUrl($bucket, $object, $timeout);
            } catch (OssException $e) {
                $ossUrl = __FUNCTION__ . ": FAILED\n";
                $ossUrl = $e->getMessage() . "\n";
                echo $e;
                echo $ossUrl;
            }
            $ossUrl = __FUNCTION__ . $signedUrl;

            $get_data = str_replace('"url":""', '"url":"' . $ossUrl . '"', $get_data);
        }
        $i ++;
    }
}
//**********替换部分网易云付费链接结束**********

//加上callback
if ($_GET['callback'] && $_POST['types']) {
//    echo 23;
    $get_data = $_GET['callback'] . '(' . $get_data . ')';
}
echo $get_data;
