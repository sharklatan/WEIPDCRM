<?php
	/*
		This file is part of WEIPDCRM.
	
		WEIPDCRM is free software: you can redistribute it and/or modify
		it under the terms of the GNU General Public License as published by
		the Free Software Foundation, either version 3 of the License, or
		(at your option) any later version.
	
		WEIPDCRM is distributed in the hope that it will be useful,
		but WITHOUT ANY WARRANTY; without even the implied warranty of
		MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
		GNU General Public License for more details.
	
		You should have received a copy of the GNU General Public License
		along with WEIPDCRM.  If not, see <http://www.gnu.org/licenses/>.
	*/
	
	error_reporting(0);
	ob_start();
	define("DCRM",true);
	require_once("manage/include/config.inc.php");
	require_once("manage/include/autofill.inc.php");
	require_once("manage/include/Mobile_Detect.php");
	require_once('manage/include/connect.php');
	header("Content-Type: text/html; charset=UTF-8");
	
	$detect = new Mobile_Detect;
	if(!$detect->isMobile()){
	    header("Location: misc.php");
	}
	$con = mysql_connect($server,$username,$password);
	if (!$con) {
		echo 'MYSQL ERROR!';
		exit();
	}
	mysql_query("SET NAMES utf8",$con);
	$select  = mysql_select_db($database,$con);
	if (!$select) {
		echo 'MYSQL ERROR!';
		exit();
	}
	if (file_exists("Release")) {
		$release = file("Release");
		$release_origin = "未命名";
		foreach ($release as $line) {
			if(preg_match("#^Origin#", $line)) {
				$release_origin = trim(preg_replace("#^(.+): (.+)#","$2", $line));
			}
			if(preg_match("#^Description#", $line)) {
				$release_description = trim(preg_replace("#^(.+): (.+)#","$2", $line));
			}
		}
	} else {
		$release_origin = '空白页';
	}
	if (isset($_GET['pid']) && is_numeric($_GET['pid'])) {
		if (isset($_GET['method']) && $_GET['method'] == "screenshot") {
			$index = 2;
			$pkg = (int)mysql_real_escape_string($_GET['pid']);
			$pkg_query = mysql_query("SELECT `PID`, `Image`, `Description`, `Width`, `Height` FROM `ScreenShots` WHERE `PID` = '".$pkg."'");
			if (!$pkg_query) {
				echo 'MYSQL ERROR!';
				exit();
			}
			$title = $pkg." - 截图";
		} else {
			$index = 1;
			$pkg = (int)mysql_real_escape_string($_GET['pid']);
			$pkg_query = mysql_query("SELECT `Name`, `Version`, `Package`, `Description`, `DownloadTimes`, `Multi`, `CreateStamp` FROM `Packages` WHERE (`ID` = '".$pkg."' AND `Stat` = '1') LIMIT 1");
			if (!$pkg_query) {
				echo 'MYSQL ERROR!';
				exit();
			}
			$pkg_assoc = mysql_fetch_assoc($pkg_query);
			if (!$pkg_assoc) {
				echo 'NO PACKAGE SELECTED!';
				exit();
			} else {
				$title = $pkg_assoc['Name'];
			}
		}
	} else {
		$index = 0;
		$title = $release_origin;
	}
?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN" "http://www.w3.org/TR/html4/loose.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
	<head>
		<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
		<title><?php echo $title; ?></title>
		<link rel="shortcut icon" href="favicon.ico" /> 
		<base target="_top">
		<link href="manage/css/iPhone.css" rel="stylesheet">
		<meta name="viewport" content="width=device-width, minimum-scale=1.0, maximum-scale=1.0, user-scalable=no">
		<style type="text/css">
			img {
				margin: 9px 0 4px 0;
			}
		</style>
	</head>
	<style type="text/css">
		label.advertisement {
			margin: 0;
			margin-bottom: -3px;
			padding: 0;
			font-size: smaller;
			line-height: 1em;
			text-align: center;
		}
		a.panel {
			color: #586c90;
			font-weight: bold;
			text-shadow: rgba(255, 255, 255, 0.75) 1px 1px 0;
		}
		panel > fieldset > a.half {
			margin-right: -4px;
		}
		panel > div#version {
			color: #4d4d70;
			font-size: 12px;
			margin-bottom: 9px;
			margin-top: -3px;
			text-align: center;
		}
	</style>
	<body class="pinstripe">
		<panel>
			<fieldset style="background-color: #FFD8F0">
				<a href="javascript:history.go(-1)">
					<img class="icon" src="CydiaIcon.png">
					<div>
						<label><?php echo $release_origin; ?></label>
					</div>
				</a>
			</fieldset>
<?php
	if ($index == 0) {
?>
			<fieldset style="background-color: transparent; border: none; margin: -4px 10px -14px 10px">
				<div>
					<div style="float: right; margin-top: 7px; text-align: center; width: 200px">
						<span style="font-size: 20px" id="welcome"><?php echo $release_origin; ?></span><br/>
						<span style="font-size: 17px" id="by"><a class="panel" href="<?php echo AUTOFILL_SITE; ?>"><?php echo AUTOFILL_FULLNAME; ?></a><br><a class="panel" href="mailto:<?php echo AUTOFILL_EMAIL; ?>"><?php echo AUTOFILL_EMAIL; ?></a></span>
					</div>
					<img src="CydiaIcon.png" style="vertical-align: middle" width="60" height="60"/>
				</div>
			</fieldset>
			<block>
				<p>欢迎来到 <?php echo AUTOFILL_MASTER; ?> 的个人源！╮(╯_╰)╭</p>
				<?php
					$q_info = mysql_query("SELECT count(*) FROM `Packages` WHERE `Stat` = '1'");
					$info = mysql_fetch_row($q_info);
					$num[0] = (int)$info[0];
				?>
				<p>目前有 <strong><?php echo $num[0]; ?></strong> 个软件包可供下载喔！</p>
				<p><?php echo $release_description; ?></p>
				<p><strong>请使用 Cydia<sup><small>™</small></sup> 添加地址：<br><a href="<?php echo(base64_decode(DCRM_REPOURL)); ?>"><?php echo(base64_decode(DCRM_REPOURL)); ?></a></strong></p>
			</block>
			<?php
				$section_query = mysql_query("SELECT `Name`, `Icon` FROM `Sections`");
				if (!$section_query) {
					echo "MYSQL ERROR!";
				}
				$txt = "";
				while ($section_assoc = mysql_fetch_assoc($section_query)) {
					$txt .= '<label>'.$section_assoc['Name'].'</label><fieldset>';
					$package_query = mysql_query("SELECT `ID`, `Name`, `Package` FROM `Packages` WHERE (`Stat` = '1' AND `Section` = '".mysql_real_escape_string($section_assoc['Name'])."') ORDER BY `ID` DESC LIMIT " . DCRM_SHOW_NUM);
					while ($package_assoc = mysql_fetch_assoc($package_query)) {
						$txt .= '<a href="index.php?pid='.$package_assoc['ID'].'"><img class="icon" src="icons/'.$section_assoc['Icon'].'" width="58" height="58"><div><label>'.$package_assoc['Name'].'</label></div></a>';
					}
					$txt .= '</fieldset>';
				}
				echo $txt;
	} elseif ($index == 1) {
?>
			<fieldset>
				<a href="cydia://package/<?php echo $pkg_assoc['Package']; ?>" id="cydialink">
					<img class="icon" src="icons/cydia.png" width="58" height="58">
					<div>
						<label>在 Cydia<sup><small>™</small></sup> 中查看</label>
					</div>
				</a>
				<a href="index.php?pid=<?php echo $_GET['pid']; ?>&method=screenshot">
					<img class="icon" src="icons/screenshots.png" width="58" height="58">
					<div>
						<label>查看软件包截图</label>
					</div>
				</a>
				<a href="<?php echo AUTOFILL_PAYPAL; ?>" target="_blank">
				    <img class="icon" src="icons/paypal.png" width="58" height="58"><div>
				    <label style="margin-left: 3px">前往 <span style="font-style: italic; font-weight: bold"><span style="color: #1a3665">Pay</span><span style="color: #32689a">Pal</span><sup><small>™</small></sup></span> 捐助</label>
				</div></a>
			</fieldset>
			<block>
			<p><?php echo "上传时间：".$pkg_assoc['CreateStamp']; ?></p>
			<p><?php echo "版本 ".$pkg_assoc['Version']." 下载次数 ".$pkg_assoc['DownloadTimes']; ?></p>
			</block>
			<block>
			<p><strong><?php echo htmlspecialchars($pkg_assoc['Description']); ?></strong></p>
			<p><?php echo $pkg_assoc['Multi']; ?></p>
			</block>
<?php
	} elseif ($index == 2) {
?>
			<label><?php if(count($pkg_query)==0){echo("该软件包暂无截图");}else{echo("截图");} ?></label>
			<div style="text-align: center; width: 320px">
			<?php
				while ($pkg_assoc = mysql_fetch_assoc($pkg_query)) {
					echo '<img src="'.$pkg_assoc['Image'].'" width="'.$pkg_assoc['Width'].'" height="'.$pkg_assoc['Height'].'"><p>'.$pkg_assoc['Description'].'</p>';
				}
			?>
			</div>
<?php
	}
?>
		</panel>
		<script>
			if (navigator.userAgent.search(/Cydia/) != -1) {
				document.body.classList.add("cydia");
				document.getElementById("cydialink").style.display="none";
			}
		</script>
		<!-- Statistics Start -->
		<script type="text/javascript">var cnzz_protocol = (("https:" == document.location.protocol) ? " https://" : " http://");document.write(unescape("%3Cspan id='cnzz_stat_icon_1000537818'%3E%3C/span%3E%3Cscript src='" + cnzz_protocol + "s11.cnzz.com/z_stat.php%3Fid%3D1000537818%26show%3Dpic1' type='text/javascript'%3E%3C/script%3E"));</script>
		<!-- Statistics End -->
	</body>
</html>