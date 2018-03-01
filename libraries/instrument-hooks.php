<?php
/*
Instrument Hooks for WordPress
Instruments Hooks for a Page. Outputs during the Shutdown Hook.
0.1
Mike Schinkel
http://mikeschinkel.com
*/

if (isset($_GET['instrument']) && $_GET['instrument']=='hooks') {

	add_action('shutdown','instrument_hooks');
	function instrument_hooks() {
		global $wpdb;
		$hooks = $wpdb->get_results("SELECT * FROM wp_hook_list ORDER BY first_call");
		$html = array();
		$html[] = '<style>#instrumented-hook-list table,#instrumented-hook-list th,#instrumented-hook-list td {border:1px solid gray;padding:2px 5px;}</style>
<div align="center" id="instrumented-hook-list">
	<table>
		<tr>
		<th>First Call</th>
		<th>Hook Name</th>
		<th>Hook Type</th>
		<th>Arg Count</th>
		<th>Called By</th>
		<th>Line #</th>
		<th>File Name</th>
		</tr>';
		foreach($hooks as $hook) {
			$html[] = "<tr>
			<td>{$hook->first_call}</td>
			<td>{$hook->hook_name}</td>
			<td>{$hook->hook_type}</td>
			<td>{$hook->arg_count}</td>
			<td>{$hook->called_by}</td>
			<td>{$hook->line_num}</td>
			<td>{$hook->file_name}</td>
			</tr>";
		}
		$html[] = '</table></div>';
		echo implode("\n",$html);
	}

	add_action('all','record_hook_usage');
	function record_hook_usage($hook){
		global $wpdb;
		static $in_hook = false;
		static $first_call = 1;
		static $doc_root;
		$callstack = debug_backtrace();
		if (!$in_hook) {
			$in_hook = true;
			if ($first_call==1) {
				$doc_root = $_SERVER['DOCUMENT_ROOT'];
				$results = $wpdb->get_results("SHOW TABLE STATUS LIKE 'wp_hook_list'");
				if (count($results)==1) {
					$wpdb->query("TRUNCATE TABLE wp_hook_list");
				} else {
					$wpdb->query("CREATE TABLE wp_hook_list (
					called_by varchar(96) NOT NULL,
					hook_name varchar(96) NOT NULL,
					hook_type varchar(15) NOT NULL,
					first_call int(11) NOT NULL,
					arg_count tinyint(4) NOT NULL,
					file_name varchar(128) NOT NULL,
					line_num smallint NOT NULL,
					PRIMARY KEY (first_call,hook_name))"
					);
				}
			}
			$args = func_get_args();
			$arg_count = count($args)-1;
			$hook_type = str_replace('do_','',
				str_replace('apply_filters','filter',
					str_replace('_ref_array','[]',
						$callstack[3]['function'])));
			$file_name = addslashes(str_replace($doc_root,'',$callstack[3]['file']));
			$line_num = $callstack[3]['line'];
			$called_by = $callstack[4]['function'];
			$wpdb->query("INSERT wp_hook_list
				(first_call,called_by,hook_name,hook_type,arg_count,file_name,line_num)
				VALUES ($first_call,'$called_by()','$hook','$hook_type',$arg_count,'$file_name',$line_num)");
			$first_call++;
			$in_hook = false;
		}
	}
}