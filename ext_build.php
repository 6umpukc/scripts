<?php

namespace _6umpukc_;

use Bitrix\Main\Web\Json;

// список используемых расширений
$extList = [
	'ui.buttons',
	'ui.forms',
	//'main.parambag',
];

$jsList = [
	// default core.js
	'/main/core/core.js' => 1,
	'/main/core/core_ajax.js' => 1,
	'/main/core/core_promise.js' => 1,
];
$cssList = [
];

function buildExtensionAssets($extList, &$jsList, &$cssList)
{
	$bitrixJsPrefix = '/bitrix/js/';
	$bitrixJsDir = $_SERVER['DOCUMENT_ROOT'] . $bitrixJsPrefix;

	foreach ($extList as $ext)
	{
		if (strpos($ext, 'main.core') !== false)
		{
			echo "Skip $ext\n";
			continue;
		}
		$extDir = str_replace('.', '/', $ext);
		$fname = $bitrixJsDir . $extDir . '/config.php';

		if (!file_exists($fname))
		{
			echo "Extension config $ext -> $fname not found.\n";
			continue;
		}

		$extConfig = include $fname;

		if (!empty($extConfig['rel']))
		{
			buildExtensionAssets($extConfig['rel'], $jsList, $cssList);
		}

		$css = $extConfig['css'];
		if (!empty($css) && !is_array($css))
		{
			$css = [$css];
		}
		foreach ($css as $v)
		{
			$path = (strpos($v, $bitrixJsPrefix) === false)? ($bitrixJsPrefix . $extDir . '/' . $v) : $v;
			$cssList[$path] = 1;
		}
		$js = $extConfig['js'];
		if (!empty($js) && !is_array($js))
		{
			$js = [$js];
		}
		foreach ($js as $v)
		{
			$path = (strpos($v, $bitrixJsPrefix) === false)? ($bitrixJsPrefix . $extDir . '/' . $v) : $v;
			$jsList[$path] = 1;
		}
	}
}

function buildExtensionsHtml($jsList, $cssList, $baseUrl = '')
{
	$result = '';

	foreach ($cssList as $path => $v)
	{
		$result .= '<link rel="stylesheet" type="text/css" href="' . $baseUrl . $path . '">' . "\n";
	}
	$result .= "\n\n";

	foreach ($jsList as $path => $v)
	{
		$result .= '<script type="text/javascript" src="' . $baseUrl . $path . '"></script>' . "\n";
	}

	return $result;
}

function buildExtensionsJson($jsList, $cssList)
{
	return Json::encode([
		'css' => $cssList,
		'js' => $jsList,
	], JSON_PRETTY_PRINT);
}

buildExtensionAssets($extList, $jsList, $cssList);

echo "\n\n" . buildExtensionsHtml($jsList, $cssList) . "\n\n";

echo "\n\n" . buildExtensionsJson($jsList, $cssList) . "\n\n";
