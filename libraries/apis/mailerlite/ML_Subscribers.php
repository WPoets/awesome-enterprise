<?php
	
	require_once dirname(__FILE__).'/base/ML_Rest.php';
	
	class ML_Subscribers extends ML_Rest
	{
		function ML_Subscribers( $api_key )
		{	
			$this->name = 'subscribers';

			parent::__construct($api_key);
		}

		function add( $subscriber = null, $resubscribe = 0 )
		{
			$subscriber['resubscribe'] = $resubscribe;

			return $this->execute( 'POST', $subscriber );
		}

		function addAll( $subscribers, $resubscribe = 0 )
		{
			$data['resubscribe'] = $resubscribe;

			$data['subscribers'] = $subscribers;

			$this->path .= 'import/';

			return $this->execute( 'POST', $data );
		}

		function get( $email = null, $history = 0 )
		{
			$this->setId( null );

			$this->path .= '?email=' . urlencode( $email );

			if ( $history )
				$this->path .= '&history=1';

			return $this->execute( 'GET' );
		}

		function remove( $email = null )
		{
			$this->path .= '?email=' . urlencode( $email );

			return $this->execute( 'DELETE' );
		}

		function unsubscribe( $email )
		{
			$this->path .= 'unsubscribe/?email=' . urlencode( $email );

			return $this->execute( 'POST' );
		}
	}

?>