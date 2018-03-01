<?php
	
	require_once dirname(__FILE__).'/base/ML_Rest.php';
	
	class ML_Campaigns extends ML_Rest
	{
		function ML_Campaigns( $api_key )
		{	
			$this->name = 'campaigns';

			parent::__construct($api_key);
		}

		function getRecipients( )
		{
			$this->path .= 'recipients/';

			return $this->execute( 'GET' );
		}

		function getOpens( )
		{
			$this->path .= 'opens/';

			return $this->execute( 'GET' );
		}

		function getClicks( )
		{
			$this->path .= 'clicks/';

			return $this->execute( 'GET' );
		}

		function getUnsubscribes( )
		{
			$this->path .= 'unsubscribes/';

			return $this->execute( 'GET' );
		}

		function getBounces( )
		{
			$this->path .= 'bounces/';

			return $this->execute( 'GET' );
		}

		function getJunk( )
		{
			$this->path .= 'junks/';

			return $this->execute( 'GET' );
		}
	}

?>