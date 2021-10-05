<?php
error_reporting(E_ALL^E_NOTICE^E_WARNING);

if ($_GET['types'] != 'lyric') {
	$error['state'] = true; //设置错误状态为真
	$error['msg'] = '无效的types参数';
}

if ($error['state']) {
	exit($error['msg']);
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

$outData = new stdClass();
$outData->lyric = new stdClass();
$outData->info = new stdClass();


$url = "https://www.jiangfei.net/music/api.php";
$data = post($url, $_GET);

$t1 = microtime(true);//计时开始

//echo $url;
//print_r($data);
//preg_match_all("/(jQuery([0-9]*)\_([0-9]*)\()\{\"lyric\"\:/", $data, $fuckjq);

//if ($fuckjq[1][0]) {
if (1 == 1) {
	//$lyricInput = str_replace(array($fuckjq[1][0], '})'), array('', '}'), $data);
	//$lyricInput = json_decode($lyricInput);
	$lyricInput = json_decode($data);

	preg_match_all("/(\[(\d+:\d+)(\.\d+)?\])(.*)/", $lyricInput->lyric, $lyricOutput->lyric);
	preg_match_all("/(\[(\d+:\d+)(\.\d+)?\])(.*)/", $lyricInput->tlyric, $lyricOutput->tlyric);
	if ($lyricOutput->tlyric[1]) {
		$lyricLines = count($lyricOutput->lyric[0]); 
		$lyricLines --;
		$tlyricLines = count($lyricOutput->tlyric[0]); 
		$tlyricLines --;
		$iLyric = 0;
		$iTlyric = 0;

		function array_remove(&$arr, $offset) { 
			array_splice($arr, $offset, 1); 
		} 

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
					$lyricOutput->mlyric[$iLyric] = $lyricOutput->lyric[1][$iLyric] . $lyricOutput->lyric[4][$iLyric] . " " . $lyricOutput->tlyric[4][$iTlyric] . "&nbsp;";
					$iLyric ++;
					$iTlyric ++;
				}
			}elseif ($lyricLines > $tlyricLines) {
				$t2 = microtime(true);
				while (($iLyric <= $lyricLines or $iTlyric <= $tlyricLines ) and (($t2-$t1)*1000) < 500) {
					$t2 = microtime(true);
					if ($lyricOutput->lyric[$lyricTimeState][$iLyric] == $lyricOutput->tlyric[$lyricTimeState][$iTlyric]) {
						$lyricOutput->mlyric[$iLyric] = $lyricOutput->lyric[1][$iLyric] . $lyricOutput->lyric[4][$iLyric] . " " . $lyricOutput->tlyric[4][$iTlyric] . "&nbsp;";
						$iLyric ++;
						$iTlyric ++;
					}else {
						$lyricOutput->mlyric[$iLyric] = $lyricOutput->lyric[1][$iLyric] . $lyricOutput->lyric[4][$iLyric] . " &nbsp;";
						$iLyric ++;
					}
				}
			}elseif ($lyricLines < $tlyricLines) {
				$t2 = microtime(true);
				while (($iLyric <= $lyricLines or $iTlyric <= $tlyricLines) and (($t2-$t1)*1000) < 500) {
					$t2 = microtime(true);
					if ($lyricOutput->lyric[$lyricTimeState][$iLyric] == $lyricOutput->tlyric[$lyricTimeState][$iTlyric]) {
						$lyricOutput->mlyric[$iTlyric] = $lyricOutput->lyric[1][$iLyric] . $lyricOutput->lyric[4][$iLyric] . " " . $lyricOutput->tlyric[4][$iTlyric] . "&nbsp;";
						$iLyric ++;
						$iTlyric ++;
					}else {
						$lyricOutput->mlyric[$iTlyric] = $lyricOutput->tlyric[1][$iTlyric] . $lyricOutput->tlyric[4][$iTlyric] . " &nbsp;";
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
			
			$outData->lyric->lyric = $mlyric;
			$outData->lyric->olyric = $lyricInput->lyric;
			$outData->lyric->tlyric = $lyricInput->tlyric;
		}else {
			$lyricLines = count($lyricOutput->lyric[0]);
			$lyricLines--;
			$i = 1;
			$outData->lyric->lyric = $lyricOutput->lyric[1][0] . $lyricOutput->lyric[4][0] . "&nbsp;";
			while ($i <= $lyricLines) {
				$outData->lyric->lyric = $outData->lyric->lyric . $lyricOutput->lyric[1][$i] . $lyricOutput->lyric[4][$i] . "&nbsp;";
				$i ++ ;
			}
		}
	}elseif($lyricOutput->lyric[1]) {
		$lyricLines = count($lyricOutput->lyric[0]);
		$lyricLines--;
		$i = 1;
		$outData->lyric->lyric = $lyricOutput->lyric[1][0] . $lyricOutput->lyric[4][0] . "&nbsp;";
		while ($i <= $lyricLines) {
			$outData->lyric->lyric = $outData->lyric->lyric . $lyricOutput->lyric[1][$i] . $lyricOutput->lyric[4][$i] . "&nbsp;";
			$i ++ ;
		}
	}
}

$outData->info->name = $_GET['name'];
$outData->info->artist = $_GET['artist'];
//print_r($outData->lyric);
//echo $outData->lyric->lyric;

/*
判断歌词有几种：
	111：外文歌曲，有翻译且时间标签正常。lyric, olyric, tlyric 均有值
	101：外文歌曲，有翻译但时间标签异常。lyric, tlyric 有值，olyric 无值
	100：中文歌曲或未提供翻译的外文歌曲。lyric 有值，olyric, tlyric 无值
	000：纯音乐或未上传歌词的歌曲。lyric, olyric, tlyric 均无值
*/

if ($outData->lyric->lyric AND $outData->lyric->olyric AND $outData->lyric->tlyric) {
	$outData->lyric->code = 111;
}elseif ($outData->lyric->lyric AND !$outData->lyric->olyric AND $outData->lyric->tlyric) {
	$outData->lyric->code = 101;
}elseif ($outData->lyric->lyric AND !$outData->lyric->olyric AND !$outData->lyric->tlyric) {
	$outData->lyric->code = 100;
}elseif (!$outData->lyric->lyric AND !$outData->lyric->olyric AND !$outData->lyric->tlyric) {
	$outData->lyric->code = 000;
}

//echo $outData->lyric->code;
//print_r($outData->lyric->lyric);
//print_r($lyricOutput);

if ($outData->lyric->code == 000) {
	echo "该歌曲为纯音乐或未上传歌词，无歌词提供下载";
}else {
	$outData->lyric->lyric = str_replace(["腾讯享有本翻译作品的著作权", "\n", "\r", '&apos;'], ["", '', '', "'"], $outData->lyric->lyric);
	$outData->lyric->lyric = str_replace('&nbsp;', "\r\n", $outData->lyric->lyric);
	$outData->lyric->lyric = preg_replace("/(.*[0-9][0-9])[0-9](\].*)/", "$1$2", $outData->lyric->lyric);
	require "CharsetConv.class.php";
	$obj = new CharsetConv('utf8', 'ansi');
	$response = $obj->convert($outData->lyric->lyric);
	header('Content-Type: application/octet-stream; charset=ansi');
	header('Content-Disposition: attachment; filename="' . $outData->info->artist . ' - ' . $outData->info->name . '.lrc"');
	echo $response;
}

?>