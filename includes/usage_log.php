<?php 

class aw2_usage_log{
	
	static function log_usage($collection=null,$module=null){
		$service = 0;
		if(isset($collection['service']) && $collection['service'] == "yes")
			$service = 1;

		$post_type = '';
		if(isset($collection['post_type']))
			$post_type = $collection['post_type'];

		$nmysqli = new SimpleMySQLi(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME, "utf8mb4", "assoc");
		$nmysqli->query("SET NAMES utf8mb4 COLLATE utf8mb4_unicode_ci");

		$sql = "
		start TRANSACTION;
		set @post_type='".$post_type."';
		set @module='".$module."';
		set @service='".$service."';
		
		SELECT @id:=ID FROM ".AWESOME_LOG_DB.".usage_log WHERE post_type=@post_type and module_slug=@module;

		IF @id is null THEN

			INSERT INTO ".AWESOME_LOG_DB.".`usage_log` (`post_type`,`module_slug`,`service`) VALUES ( '".$post_type."','".$module."','".$service."');
		ELSE
			UPDATE ".AWESOME_LOG_DB.".`usage_log` SET count=count+1
			WHERE ID = @id;
		END IF;
			
		COMMIT;
		";
				
		$obj = $nmysqli->multi_query($sql);
		$nmysqli->close();
	}
}
