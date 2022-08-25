<?php

//NOTE скрипт для копирования языковых файлов из заданной директории

namespace _6umpukc_;

use RecursiveIteratorIterator;
use RecursiveDirectoryIterator;

if (php_sapi_name() === 'cli')
{
	$_SERVER['DOCUMENT_ROOT'] = dirname(dirname(__DIR__));
	define('BX_BUFFER_USED', true);
	require $_SERVER['DOCUMENT_ROOT'] . '/bitrix/modules/main/cli/bootstrap.php';
}

$srcLang = 'ru';
$destLang = 'en';

$dirs = [
	'/local/templates/',
	'/local/components/',
];

/*
$dirs = [
	'/bitrix/modules/citrus.arealty',
	'/bitrix/modules/citrus.arealtypro',
	'/bitrix/modules/citrus.core',
	'/bitrix/modules/citrus.forms',

	'/bitrix/components/citrus',
	'/bitrix/components/citrus.arealty',
	'/bitrix/components/citrus.core',
	'/bitrix/components/citrus.forms',

	'/bitrix/templates/citrus_arealty3',
];

$dirs = [
	'/bitrix/modules/wc.arealty',
	'/bitrix/modules/wc.arealtypro',
	'/bitrix/modules/wc.core',
	'/bitrix/modules/wc.forms',

	'/bitrix/components/wc',
	'/bitrix/components/wc.arealty',
	'/bitrix/components/wc.core',
	'/bitrix/components/wc.forms',

	'/bitrix/templates/wc_arealty3',
];
*/

function copyLangFolder($name, $srcLang, $destLang, $rewrite = false)
{
	$dest = dirname($name) . DIRECTORY_SEPARATOR . $destLang;
	echo 'Copy files from ' . $name . "\n\t" . ' to ' . $dest . " ...\n";
	return;

	//TODO!!! check folder
	CopyDirFiles($name, $dest, $rewrite, true);
};

function processFolder($srcDir, $srcLang, $destLang)
{
	$basePath = $_SERVER['DOCUMENT_ROOT'] . $srcDir;
	if (!is_dir($basePath))
	{
		return;
	}

	echo 'Process: ' . $srcDir . "\n";

	$it = new RecursiveIteratorIterator(
		new RecursiveDirectoryIterator(
			$basePath,
			RecursiveDirectoryIterator::SKIP_DOTS
		),
		RecursiveIteratorIterator::SELF_FIRST
	);
	foreach ($it as $f)
	{
		$name = $f->getPathname();
		if (!$f->isDir())
		{
			continue;
		}
		$prefix = DIRECTORY_SEPARATOR . 'lang' . DIRECTORY_SEPARATOR . $srcLang;
		$l = strlen($prefix);
		if ((strpos($name, '.git') !== false)
				|| (strpos($name, '.dev') !== false)
				|| (strpos($name, '.updates') !== false)
				|| (strpos($name, 'vendor') !== false)
				|| (strpos($name, 'install') !== false)
				|| (substr($name, -$l) != $prefix)
			)
		{
			continue;
		}

		copyLangFolder($name, $srcLang, $destLang);
	}

	echo "\n\n";
};

foreach ($dirs as $srcDir)
{
	processFolder($srcDir, $srcLang, $destLang);
}
