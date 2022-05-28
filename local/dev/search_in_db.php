<?php

//NOTE поиск фразы по всем таблицам

namespace _6umpukc_;

const IGNORED_TABLES = [
	'b_numerator_sequence',
	'b_sale_basket',
	'b_user_profile_history',
	'b_user_profile_record',
	//'b_site_template',
];

if (php_sapi_name() === 'cli')
{
	$_SERVER['DOCUMENT_ROOT'] = dirname(dirname(__DIR__));
	define('BX_BUFFER_USED', true);
	require $_SERVER['DOCUMENT_ROOT'] . '/bitrix/modules/main/cli/bootstrap.php';
}

function searchAllDB($search, $showQueryTablesOnly = false)
{
	global $DB;

	$outFile = __DIR__ . '/.search_result.log';
	unlink($outFile);

    $sql = 'show tables';
    $rs = $DB->Query($sql);

	while ($r = $rs->Fetch())
	{
		$table = reset($r);

		if (in_array($table, IGNORED_TABLES))
		{
			continue;
		}

		$sql_search = 'select * from `' . $table . '` where ';
		$sql_search_fields = [];
		$sql2 = 'SHOW COLUMNS FROM ' . $table;

		$rs2 = $DB->Query($sql2);

		while ($column = $rs2->Fetch())
		{
			$colum = reset($column);
			$sql_search_fields[] = '`' . $colum . "` like('%" . $search . "%')";
		}

		$sql_search .= implode("\n\t" . ' OR ', $sql_search_fields);

		echo $sql_search . "\n\n";
		flush();

		if ($showQueryTablesOnly)
		{
			continue;
		}

		$rs3 = $DB->Query($sql_search);
		if ($rs3->SelectedRowsCount())
		{
			file_put_contents($outFile,
				'HAS RESULT: ' . $rs3->SelectedRowsCount() . ' rows on '  . $sql_search . "\n\n",
				FILE_APPEND);
		}
		else
		{
			file_put_contents($outFile,
				'NOT FOUND: on '  . $sql_search . "\n\n",
				FILE_APPEND);

		}
    }
}

searchAllDB('Тут фраза для поиска', true);
