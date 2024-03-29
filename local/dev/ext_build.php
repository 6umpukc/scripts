<?php

//NOTE По списку используемых расширений собирает подключаемые js и css

namespace _6umpukc_;

use Bitrix\Main\Web\Json;
use Bitrix\Main\Config\Configuration;

$extList = Configuration::getValue('ext_build_list');

$jsList = [
	// default core.js
	'/bitrix/js/main/core/core.js' => 1,
	'/bitrix/js/main/core/core_ajax.js' => 1,
	'/bitrix/js/main/core/core_promise.js' => 1,
];
$cssList = [
];

if (php_sapi_name() === 'cli')
{
	$_SERVER['DOCUMENT_ROOT'] = dirname(dirname(__DIR__));
	define('BX_BUFFER_USED', true);
	require $_SERVER['DOCUMENT_ROOT'] . '/bitrix/modules/main/cli/bootstrap.php';
}

function buildExtensionAssets($extList, &$jsList, &$cssList)
{
	$bitrixJsPrefix = '/bitrix/js/';
	$bitrixJsDir = $_SERVER['DOCUMENT_ROOT'] . $bitrixJsPrefix;

	foreach ($extList as $ext => $type)
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

file_put_contents(__DIR__ . '/.assets.json', buildExtensionsJson($jsList, $cssList));
file_put_contents(__DIR__ . '/.assets.html', buildExtensionsHtml($jsList, $cssList));
