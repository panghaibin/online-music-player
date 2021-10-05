<?php
error_reporting(E_ALL^E_NOTICE^E_WARNING);

//对发送过来的数据进行检查
$error['msg'] = ' ';
//检查类型是否为歌词
if ($_POST['types'] != 'lyric') {
	$error['msg'] .= '<br>无效的types参数';
}

//检查文件名是否存在
if ($_POST['name']) {
	$inData['name'] = $_POST['name'];
}else {
	$error['msg'] .= '<br>无效的name参数';
}

//检查字号，限定范围
if ($_POST['font-size'] >= 6 and $_POST['font-size'] <= 20) {
	$inData['font-size'] = $_POST['font-size'];
}else {
	$error['msg'] .= '<br>无效的font-size参数';
}

//检查字体，设置对应字体名称
if ($_POST['font-family'] == 00) {
	$inData['font-family'] = 'dx';//等线
}elseif ($_POST['font-family'] == 01) {
	$inData['font-family'] = 'msyh';//微软雅黑
}elseif ($_POST['font-family'] == 02) {
	$inData['font-family'] = 'hksnt';//华康少女体
}else {
	$error['msg'] .= '<br>无效的font-family参数';
}

//检查分栏，1不分栏，2两栏，3三栏
if ($_POST['column'] <= 3 and $_POST['column'] >= 1) {
	$inData['column'] = $_POST['column'];
}else {
	$error['msg'] .= '<br>无效的column参数';
}

//检查二维码(TO DO)
if ($_POST['qr-code'] == 'off' or $_POST['source'] == 'kugou') {
	$inData['qrstate'] = 'off';
}elseif ($_POST['qr-code'] == 'on') {
	$inData['qrstate'] = 'on';
	if ($_POST['source'] == 'netease') {
		$inData['qrurl'] = 'https://music.163.com/#/song?id=' . $_POST['id'];
	}elseif ($_POST['source'] == 'tencent') {
		$inData['qrurl'] = 'https://y.qq.com/n/yqq/song/' . $_POST['id'] . '.html';
	}elseif ($_POST['source'] == 'xiami') {
		$inData['qrurl'] = 'https://www.xiami.com/song/' . $_POST['id'];
	}
}else {
	$error['msg'] .= '<br>无效的qrcode参数';
}


//若错误状态存在，则直接退出程序
if ($error['msg'] != ' ') {
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

$url = "https://www.jiangfei.net/music/api.php";
$data = post($url, $_POST);

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
					$lyricOutput->mlyric[$iLyric] =  $lyricOutput->lyric[4][$iLyric] . "&nbsp;" . $lyricOutput->tlyric[4][$iTlyric] . "&nbsp;";
					$iLyric ++;
					$iTlyric ++;
				}
			}elseif ($lyricLines > $tlyricLines) {
				$t2 = microtime(true);
				while (($iLyric <= $lyricLines or $iTlyric <= $tlyricLines ) and (($t2-$t1)*1000) < 500) {
					$t2 = microtime(true);
					if ($lyricOutput->lyric[$lyricTimeState][$iLyric] == $lyricOutput->tlyric[$lyricTimeState][$iTlyric]) {
						$lyricOutput->mlyric[$iLyric] =  $lyricOutput->lyric[4][$iLyric] . "&nbsp;" . $lyricOutput->tlyric[4][$iTlyric] . "&nbsp;";
						$iLyric ++;
						$iTlyric ++;
					}else {
						$lyricOutput->mlyric[$iLyric] = $lyricOutput->lyric[4][$iLyric] . "&nbsp;";
						$iLyric ++;
					}
				}
			}elseif ($lyricLines < $tlyricLines) {
				$t2 = microtime(true);
				while (($iLyric <= $lyricLines or $iTlyric <= $tlyricLines) and (($t2-$t1)*1000) < 500) {
					$t2 = microtime(true);
					if ($lyricOutput->lyric[$lyricTimeState][$iLyric] == $lyricOutput->tlyric[$lyricTimeState][$iTlyric]) {
						$lyricOutput->mlyric[$iTlyric] =  $lyricOutput->lyric[4][$iLyric] . "&nbsp;" . $lyricOutput->tlyric[4][$iTlyric] . "&nbsp;";
						$iLyric ++;
						$iTlyric ++;
					}else {
						$lyricOutput->mlyric[$iTlyric] = $lyricOutput->tlyric[4][$iTlyric] . "&nbsp;";
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
			$outData->lyric->lyric = $lyricOutput->lyric[4][$i] . "&nbsp;";
			while ($i <= $lyricLines) {
				$outData->lyric->lyric = $outData->lyric->lyric . $lyricOutput->lyric[4][$i] . "&nbsp;";
				$i ++ ;
			}
		}
	}elseif($lyricOutput->lyric[1]) {
		$lyricLines = count($lyricOutput->lyric[0]);
		$lyricLines--;
		$i = 1;
		$outData->lyric->lyric = $lyricOutput->lyric[4][$i] . "&nbsp;";
		while ($i <= $lyricLines) {
			$outData->lyric->lyric = $outData->lyric->lyric . $lyricOutput->lyric[4][$i] . "&nbsp;";
			$i ++ ;
		}
	}
}

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

if ($outData->lyric->code == 000) {
	exit("该歌曲为纯音乐或未上传歌词，无歌词提供下载");
}else {
	$outData->lyric->lyric = str_replace(["腾讯享有本翻译作品的著作权", "\n", "\r", '&apos;'], ["", '', '', "'"], $outData->lyric->lyric);
	$outData->lyric->lyric = str_replace('&nbsp;', "\n", $outData->lyric->lyric);
}


require_once('tcpdf.php');

class MC_TCPDF extends TCPDF {

	/**
	 * Print chapter
	 * @param $num (int) chapter number
	 * @param $title (string) chapter title
	 * @param $file (string) name of the file containing the chapter body
	 * @param $mode (boolean) if true the chapter body is in HTML, otherwise in simple text.
	 * @public
	 */
	public function PrintChapter($num, $title, $file, $mode=false, $column, $fontfamily, $fontsize) {
		// add a new page
		//$this->AddPage();
		// disable existing columns
		$this->resetColumns();
		// print chapter title
		//$this->ChapterTitle($num, $title);
		// set columns
		if ($column == 2) {
			$this->setEqualColumns(2, 86);//前一个是分栏数。后一个是分栏大小：3-57，2-86
		}else {
			$this->setEqualColumns(3, 57);
		}
		
		// print chapter body
		$this->ChapterBody($file, $mode, $fontfamily, $fontsize);
	}

	/**
	 * Set chapter title
	 * @param $num (int) chapter number
	 * @param $title (string) chapter title
	 * @public
	 */
	public function ChapterTitle($num, $title) {
		$this->SetFont('dx', '', 14);
		$this->SetFillColor(200, 220, 255);
		$this->Cell(180, 6, 'Chapter '.$num.' : '.$title, 0, 1, '', 1);
		$this->Ln(4);
	}

	/**
	 * Print chapter body
	 * @param $file (string) name of the file containing the chapter body
	 * @param $mode (boolean) if true the chapter body is in HTML, otherwise in simple text.
	 * @public
	 */
	public function ChapterBody($file, $mode=false, $fontfamily, $fontsize) {
		$this->selectColumn();
		// get esternal file content
		$content = $file;
		// set font
		$this->SetFont($fontfamily , '', $fontsize);//字体，不知道，字号
		$this->SetTextColor(50, 50, 50);
		// print content
		if ($mode) {
			// ------ HTML MODE ------
			$this->writeHTML($content, true, false, true, false, 'J');
		} else {
			// ------ TEXT MODE ------
			$this->Write(0, $content, '', 0, 'J', true, 0, false, true, 0);
		}
		$this->Ln();
	}
} // end of extended class


//实例化
//$pdf = new TCPDF('P', 'mm', 'A4', true, 'UTF-8', false);
//$pdf = new MC_TCPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);
$pdf = new MC_TCPDF('P', 'mm', 'A4', true, 'UTF-8', false);

// 设置文档信息
$pdf->SetCreator('hbtech');//设置创建者
$pdf->SetAuthor('HaiBinTechnology');//设置作者
$pdf->SetTitle($inData['name']);//设置文件的title
$pdf->SetSubject('Print Lyric into PDF');//设置主题
$pdf->SetKeywords('Lyric, Print, ' . $inData['name']);//设置关键词

//false 为去掉默认的页头页脚。比如那个横线
$pdf->setPrintHeader(false);
$pdf->setPrintFooter(false);
/*
// 设置页眉和页脚信息
$pdf->SetHeaderData('', 30, 'Sia', 'The Gretest', array(0,64,255), array(0,64,128));
$pdf->setFooterData(array(0,64,0), array(0,64,128));

// 设置页眉和页脚字体
$pdf->setHeaderFont(Array($inData['font-family'], '', '10'));
$pdf->setFooterFont(Array($inData['font-family'], '', '8'));
*/

//添加字体
$fontname = TCPDF_FONTS::addTTFfont('./fonts/dx.ttf', 'dx', '', 32);
$fontname = TCPDF_FONTS::addTTFfont('./fonts/msyh.ttf', 'msyh', '', 32);
$fontname = TCPDF_FONTS::addTTFfont('./fonts/hksnt.ttf', 'hksnt', '', 32);

// 设置默认等宽字体
$pdf->SetDefaultMonospacedFont($inData['font-family']);

// 设置间距
$pdf->SetMargins(15, 16, 15);
$pdf->SetHeaderMargin(5);
$pdf->SetFooterMargin(10);

// 设置分页
$pdf->SetAutoPageBreak(TRUE, 16);

// set image scale factor
$pdf->setImageScale(1.25);

// set default font subsetting mode
$pdf->setFontSubsetting(true);

//设置字体
$pdf->SetFont($inData['font-family'], '', 18);

$pdf->AddPage();

$pdf->Write(0, $inData['name'], '', 0, 'C', true, 0, false, false, 0);//标题

//判断是否需要输出二维码
if ($inData['qrstate'] == 'on') {
	$style = array(
		'border' => false,
		'padding' => 0,
		'fgcolor' => array(0,0,0),
		'bgcolor' => false, //array(255,255,255)
		'module_width' => 1, // width of a single module in points
		'module_height' => 1 // height of a single module in points
	);
	$pdf->write2DBarcode($inData['qrurl'], 'QRCODE,B', 170, 5, 80, 80, $style, 'N');//二维码
}

//判断分栏
if ($inData['column'] == 1) {
	$pdf->SetFont($inData['font-family'], '', $inData['font-size']);
	$pdf->Write(0, $outData->lyric->lyric, '', 0, 'C', true, 0, false, false, 0);
}else {
	$pdf->PrintChapter(0, '', $outData->lyric->lyric, false, $inData['column'], $inData['font-family'], $inData['font-size']);
}

//输出PDF
$pdf->Output($inData['name'] . '(' . $_POST['source'] . $_POST['id'] . ').pdf', 'I');

?>