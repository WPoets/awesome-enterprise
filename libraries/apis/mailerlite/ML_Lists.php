<?php
	
	require_once dirname(__FILE__).'/base/ML_Rest.php';
	
	class ML_Lists extends ML_Rest
	{
		function ML_Lists( $api_key )
		{	
			$this->name = 'lists';

			parent::__construct($api_key);
		}

		function getActive( )
		{
			$this->path .= 'active/';

			return $this->execute( 'GET' );
		}

		function getUnsubscribed( )
		{
			$this->path .= 'unsubscribed/';

			return $this->execute( 'GET' );
		}

		function getBounced( )
		{			
			$this->path .= 'bounced/';

			return $this->execute( 'GET' );
		}
	}

?>