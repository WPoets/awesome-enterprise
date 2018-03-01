<?php
/**
 * @version e4c2e8edac362acab7123654b9e73432 / 1.0
 * @author TotalSend <support@totalsend.com>
 * @see http://www.totalsend.com
 * @see Help: http://www.totalsend.com/totalsend/help/integration/wordpress/
 * Adapted from TotalSend WordPress Integration plugin
 */

class TotalSend
{
	private static $option_name = 'totalsend-subs';
	private $_response;
	private $_email;
	private $_options;
	private $_SessionID;
	private $_IpAddress;
	private $_successMessages;
	private $_subscriptionErrors;
	private $_unsubscriptionErrors;
	private $_targetListID;
	private $_customFields;

	public function __construct( $login = '', $password = '' )
	{

		$this->_options['api_url'] = 'https://app.totalsend.com/api.php?';

		if ( empty( $login ) ) {
			$response = array(
				'error' => 'true',
				'data' => 'You must provide your TotalSend login email.',
			);

			return $response;
		} else {
			$this->_options['login'] = $login;
		}

		if ( empty( $password ) ) {
			$response = array(
				'error' => 'true',
				'data' => 'You must provide your TotalSend password.',
			);

			return $response;
		} else {
			$this->_options['password'] = $password;
		}

		$this->_response = array(
			'msg' => '',
			'success' => false
		);
		$this->_SessionID = null;
		$this->_IpAddress = $this->_getIpAddress();
		$this->_successMessages = array(
			'subscription_success' => __('Successfully subscribed'),
			'subscription_success_pending' => __('Please check your inbox to confirm subscription'),
			'unsubscription_success' => __('Successfully unsubscribed')
		);
		$this->_subscriptionErrors = array(
			'2' => __('Email address is missing'),
			'5' => __('Invalid email address format'),
			'9' => __('Email address already exists in the target list'),
		);
		$this->_unsubscriptionErrors = array(
			'3' => __('Email address must be provided'),
			'6' => __('Invalid email address format'),
			'7' => __('Email address doesn\'t exist in the list'),
			'9' => __('Email address already unsubscribed'),
		);

		$this->_customFields = array();
	}

	private function _getIpAddress()
	{
		if (isset($_SERVER)) {
			if (isset($_SERVER["HTTP_X_FORWARDED_FOR"])) {
				$ip_addr = $_SERVER["HTTP_X_FORWARDED_FOR"];
			} elseif (isset($_SERVER["HTTP_CLIENT_IP"])) {
				$ip_addr = $_SERVER["HTTP_CLIENT_IP"];
			} else {
				$ip_addr = $_SERVER["REMOTE_ADDR"];
			}
		} else {
			if (getenv('HTTP_X_FORWARDED_FOR')) {
				$ip_addr = getenv('HTTP_X_FORWARDED_FOR');
			} elseif (getenv('HTTP_CLIENT_IP')) {
				$ip_addr = getenv('HTTP_CLIENT_IP');
			} else {
				$ip_addr = getenv('REMOTE_ADDR');
			}
		}
		return $ip_addr;
	}

	public function setEmail($email)
	{
		if (!$this->_email = $this->_validateEmail($email)) {
			$this->_setResponse($this->_subscriptionErrors[5]);
			$this->_sendResponse();
		}
		return $this->_email;
	}

	public function setTargetList($listId)
	{
		return $this->_targetListID = $listId;
	}

	public function setCustomFields($customFields = array())
	{
		return $this->_customFields = $customFields;
	}

	public function subscribe()
	{
		if (!$this->_email) {
			$this->_setResponse($this->_subscriptionErrors[7]);
			$this->_sendResponse();
		}

//		$this->init();
		try {
			$params = array(
				'ListID' => $this->_targetListID,
				'EmailAddress' => $this->_email,
				'IPAddress' => $this->_IpAddress
			);
			if (count($this->_customFields)) {
				foreach ($this->_customFields as $key => $val) {
					$params[$key] = $val;
				}
			}

			$response = $this->_getResponse($this->_getCommandUrl('Subscriber.Subscribe', $params));

			if ($response['Success']) {
				if ('Subscribed' == $response['Subscriber']['SubscriptionStatus'])
					$this->_setResponse($this->_successMessages['subscription_success'], true);
				elseif ('Confirmation Pending' == $response['Subscriber']['SubscriptionStatus'])
					$this->_setResponse($this->_successMessages['subscription_success_pending'], true);
			} else {
				if(is_array($response['ErrorCode']) && count($response['ErrorCode'])) {
					// we need to show up only first problem which we know
					foreach ($response['ErrorCode'] as $errorCode) {
						if (isset($this->_subscriptionErrors[$errorCode]))
							$this->_setResponse($this->_subscriptionErrors[$errorCode]);
					}
				} else {
					$this->_setResponse($this->_subscriptionErrors[$response['ErrorCode']]);
				}
			}
		} catch (Exception $e) {
			$this->_setResponse($e);
		}
		$this->_sendResponse();
	}

	public function unsubscribe()
	{
		if (!$this->_email) {
			$this->_setResponse($this->_subscriptionErrors[7]);
			$this->_sendResponse();
		}

		$this->init();
		try {
			$response = $this->_getResponse($this->_getCommandUrl('Subscriber.Unsubscribe',
				array(
					'ListID' => $this->_targetListID,
					'EmailAddress' => $this->_email,
					'IPAddress' => $this->_IpAddress
				)
			));
			if ($response['Success']) {
				$this->_setResponse($this->_successMessages['unsubscription_success'], true);
			} else {
				if(is_array($response['ErrorCode']) && count($response['ErrorCode'])) {
					// we need to show up only first problem which we know
					foreach ($response['ErrorCode'] as $errorCode) {
						if (isset($this->_subscriptionErrors[$errorCode]))
							$this->_setResponse($this->_unsubscriptionErrors[$errorCode]);
					}
				} else {
					$this->_setResponse($this->_subscriptionErrors[$response['ErrorCode']]);
				}
			}
		} catch (Exception $e) {
			$this->_setResponse($e);
		}
		$this->_sendResponse();
	}


	private function _getResponse($url)
	{
		$results = null;
		$parts = parse_url($url);
		parse_str($parts['query'], $fields);

		if(isset($fields['Command']) && $fields['Command'] != 'User.Login')
		{
			$results = file_get_contents($url);
		}
		else
		{
			$url = $parts['scheme'].'://'.$parts['host'].$parts['path'];

			// Get cURL resource
			$curl = curl_init();

			// Set some options
			curl_setopt_array($curl, array(
				CURLOPT_RETURNTRANSFER => 1,
				CURLOPT_URL => $url,
				CURLOPT_POST => count($fields),
				CURLOPT_POSTFIELDS => http_build_query($fields),
				CURLOPT_SSL_VERIFYPEER => false,
			));

			// Send the request & save response to $resp
			$results = curl_exec($curl);

			if(!$results){
				die('Error: "' . curl_error($curl) . '" - Code: ' . curl_errno($curl));
			}

			// Close request to clear up some resources
			curl_close($curl);
		}

		$json_results = json_decode($results, true);
		return $json_results;
	}

	private function _getCommandUrl($command, $params = array())
	{

		if ($command == 'Subscriber.Subscribe' || $command == 'Subscriber.Unsubscribe') {

			$url = $this->_options['api_url'] . sprintf(
					'Command=%s&ResponseFormat=JSON',
					$command
				);

		} else {

			if (!$this->_SessionID) {
				$url = $this->_options['api_url'] . sprintf(
						'Command=User.Login&Username=%s&Password=%s&ResponseFormat=JSON',
						$this->_options['login'],
						$this->_options['password']
					);
				$response = $this->_getResponse($url);
				if (true == $response['Success'])
					$this->_SessionID = $response['SessionID'];
				else {
					$this->_setResponse(__(serialize($url) . 'TotalSend credentials are incorrect'));
					$this->_sendResponse();
				}
			}

			$url = $this->_options['api_url'] . sprintf(
					'Command=%s&SessionID=%s&ResponseFormat=JSON',
					$command,
					$this->_SessionID
				);

		}

		if (count($params)) {
			foreach ($params as $paramKey => $val) {
				if (!empty($val))
					if (!is_array($val)) {
						$url .= sprintf('&%s=%s', $paramKey, htmlentities(urlencode($val)));
					} else {
						foreach ($val as $valEl) {
							$url .= sprintf('&%s=%s', $paramKey . '[]', htmlentities(urlencode($valEl)));
						}

					}
			}
		}
		return $url;
	}

	private function _validateEmail($email)
	{
		return is_email(trim($email));
	}

	private function _setResponse($msg, $success = false)
	{
		$this->_response['msg'] = $msg;
		$this->_response['success'] = $success;
	}

	private function _sendResponse()
	{
		die(json_encode($this->_response));
	}


	public function getSubscriberLists()
	{
		return $this->_getResponse($this->_getCommandUrl('Lists.Get', array('OrderField' => 'Name', 'OrderType' => 'ASC')));
	}

	public function getConnection()
	{
		$url = $this->_options['api_url'] . sprintf(
				'Command=User.Login&Username=%s&Password=%s&ResponseFormat=JSON',
				$this->_options['login'],
				$this->_options['password']
			);

		$response = $this->_getResponse($url);
		return $response;
	}

	public function getSubscriberListFields($listId)
	{
		$result = array();

//		$this->init();
		$this->setTargetList($listId);
		try {
			$params = array(
				'SubscriberListID' => $this->_targetListID,
				'OrderField' => 'CustomFieldID',
				'OrderType' => 'ASC',
			);
			$response = $this->_getResponse($this->_getCommandUrl('CustomFields.Get', $params));
			if ($response['Success']) {
				$result = $response['CustomFields'];
			}
		} catch (Exception $e) {
			// Nothing to do here.
		}
		return $result;
	}
}
// ============================================================================
