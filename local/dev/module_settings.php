<?php

//TODO!!!
//NOTE выгрузить настройки модулей

namespace Citrus\Scripts\Settings;

use Bitrix\Main\Config;

if (php_sapi_name() === 'cli')
{
	$_SERVER['DOCUMENT_ROOT'] = dirname(dirname(__DIR__));
	define('BX_BUFFER_USED', true);
	require $_SERVER['DOCUMENT_ROOT'] . '/bitrix/modules/main/cli/bootstrap.php';
}

final class Options
{
	const DEFAULT_MODULES = [
		'wsrubi.smtp' => 1,
	];

	public static function export($filter = null)
	{
		if ($filter === null)
		{
			$filter = static::DEFAULT_MODULES;
		}
		$result = [];
		foreach ($filter as $module => $_)
		{
			$result[$module] = Config\Option::getForModule($module);
			ksort($result[$module]);
		}

		ksort($result);
		return "\n" . '$bitrixSettings = ' . var_export($result, true) . ";\n\n";
	}

	public function import($settings)
	{
		foreach ($settings as $module => $options)
		{
			foreach ($options as $name => $value)
			{
				Config\Option::set($module, $name, $value);
			}
		}
	}
}

// --- examples

echo Options::export();

/* result example:

$bitrixSettings = array (
  'wsrubi.smtp' =>
  array (
    'active' => 'Y',
    'addrtovalidation' => NULL,
    'convert_to_utf8' => NULL,
    'onlyposting' => NULL,
    'posting' => NULL,
    'remove_headers' => '[]',
    'save_email_error' => NULL,
    'settings_smtp_host' => '3',
    'settings_smtp_log' => NULL,
    'settings_smtp_login' => '1',
    'settings_smtp_password' => '2',
    'settings_smtp_port' => '25',
    'settings_smtp_testing_email' => '',
    'settings_smtp_testing_from' => '',
    'settings_smtp_type_auth' => 'login',
    'settings_smtp_type_encryption' => 'tls',
  ),
);
Options::import($bitrixSettings);

*/
