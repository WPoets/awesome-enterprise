<?php
namespace aw2\gmail;

//Select messagestatus as ALL or UNSEEN which is the unread email
define("MESSAGESTATUS","ALL");

//Which folders or label do you want to access? - Example: INBOX, All Mail, Trash, labelname 
//Note: It is case sensitive
define("IMAPMAINBOX","INBOX");

//Gmail Connection String
define("IMAPADDRESS","{imap.gmail.com:993/imap/ssl/novalidate-cert}");	 


\aw2_library::add_service('gmail.overview','email overview',['namespace'=>__NAMESPACE__]);
function overview($atts,$content=null,$shortcode){	

		if(\aw2_library::pre_actions('all',$atts,$content,$shortcode)==false)return;
		extract(\aw2_library::shortcode_atts( array(
			'config'=>'',
			'uid'=>''
			), $atts) );	
			
		$connection=gmail_connection($config);	

		//Fetch email data by uid
		$email_data=single_email_data($connection,$config,$uid,"no");		
		
		if(isset($email_data['row'])){
			
			//fetch structure
			$structure = imap_fetchstructure($connection, $email_data['row']['msgno']);
		
			/*get attachment is count
			  check attachment is available or not
			*/  
			
			$attachment_count=attachment($connection,$structure,$email_data['row']['msgno'],'no');			
			$email_data['row']['attachment_status']=($attachment_count > 0) ? 'yes' : 'no';
			
			
			//Because attachments can be problematic this logic will default to skipping the attachments    
			$message = imap_fetchbody($connection,$email_data['row']['msgno'],1.1);
				 if ($message == "") { // no attachments is the usual cause of this
				  $message = imap_fetchbody($connection, $email_data['row']['msgno'], 1);
			}
			$email_data['row']['message']=$message;

		}
		imap_close($connection);
		return $email_data;
		
}



//by default attachment download set to no
//this function accept 6  parameters
function attachment($connection,$structure,$email_number,$attachment_save="no",$lapp_id="",$config=null){
	
	$counter=0;
	/* if any attachments found... */
	
			if(isset($structure->parts) && count($structure->parts)) 
			{
					for($i = 0; $i < count($structure->parts); $i++) 
					{
							$attachments[$i] = array(
									'is_attachment' => false,
									'filename' => '',
									'name' => '',
									'attachment' => ''
							);

							if($structure->parts[$i]->ifdparameters) 
							{
									foreach($structure->parts[$i]->dparameters as $object) 
									{
											if(strtolower($object->attribute) == 'filename') 
											{
													$attachments[$i]['is_attachment'] = true;
													$attachments[$i]['filename'] = $object->value;
											}
									}
							}

							if($structure->parts[$i]->ifparameters) 
							{
									foreach($structure->parts[$i]->parameters as $object) 
									{
											if(strtolower($object->attribute) == 'name') 
											{
													$attachments[$i]['is_attachment'] = true;
													$attachments[$i]['name'] = $object->value;
											}
									}
							}

							if($attachments[$i]['is_attachment']) 
							{
								$counter ++;
									$attachments[$i]['attachment'] = imap_fetchbody($connection, $email_number, $i+1);

									/* 3 = BASE64 encoding */
									if($structure->parts[$i]->encoding == 3) 
									{ 
											$attachments[$i]['attachment'] = base64_decode($attachments[$i]['attachment']);
									}
									/* 4 = QUOTED-PRINTABLE encoding */
									elseif($structure->parts[$i]->encoding == 4) 
									{ 
											$attachments[$i]['attachment'] = quoted_printable_decode($attachments[$i]['attachment']);
									}
							}
					}
			}
			if($attachment_save==="no"){
				return $counter;
			}
			else if($attachment_save==="yes"){
				
				
				/* iterate through each attachment and save it */
				if(!empty($attachments)){
					foreach($attachments as $attachment)
					{
						//echo 'reached here';
							if($attachment['is_attachment'] == 1)
							{
									$filename = $attachment['name'];
									if(empty($filename)) $filename = $attachment['filename'];

									if(empty($filename)) $filename = time() . ".dat";

									 
									$path=$config['path'].$lapp_id."/";
									
									if(!is_dir($path)){
										//echo "Not Found";
										mkdir($path,0755,true);
									}  
									//put a unique token before 
									$fp = fopen($path . time() .'_'. $filename, "w+");
									fwrite($fp, $attachment['attachment']);
									fclose($fp);
							}

					}
				}
			}
}


\aw2_library::add_service('gmail.search','email listing',['namespace'=>__NAMESPACE__]);

function search($atts,$content=null,$shortcode){
			
		$result=array();
		
		if(\aw2_library::pre_actions('all',$atts,$content,$shortcode)==false)return;
		extract(\aw2_library::shortcode_atts( array(
			'config'=>'',
			'criteria'=>''
			), $atts) );	
		
	$connection=gmail_connection($config);
	
		
	//Grab all the emails inside the inbox
	$uids = imap_search($connection,MESSAGESTATUS,SE_UID);
	
	$emails=array();
	foreach($uids as $uid){	
	    
	    //validation is required for email,
		//parameter yes is passed for validation
		
		$email_data=single_email_data($connection,$config,$uid,"yes");
		
		
		if(!empty($email_data)){
			$emails[$uid]=$email_data;
		}
	}
	
    $result['status']="success";
	$result['rows']=$emails;	
	imap_close($connection);
	return $result;
	
	 
}

\aw2_library::add_service('gmail.delete_email','email delete',['namespace'=>__NAMESPACE__]);

function delete_email($atts,$content=null,$shortcode){
	if(\aw2_library::pre_actions('all',$atts,$content,$shortcode)==false)return;
		extract(\aw2_library::shortcode_atts( array(
			'config'=>'',
			'uid'=>'',
			'msgno'=>''
			), $atts) );	
		
		$connection=gmail_connection($config);
		//$header = imap_fetch_overview($connection,$uid,FT_UID);	
		//$emails=json_decode(json_encode($header), True);				
		$rr=imap_delete($connection, $uid,FT_UID); 
		//echo "<br> UID :- ";$uid;
		
		//$tt =imap_mail_move($connection, "$msgno:$msgno", '[Gmail]/Trash');
		
		imap_expunge($connection);
		// close the connection
		imap_close($connection,CL_EXPUNGE);
		$result['status']="success";
		$result['message']="record deleted successfully";
	
	return $result;
}


\aw2_library::add_service('gmail.save_attachments','save attachments',['namespace'=>__NAMESPACE__]);

function save_attachments($atts,$content=null,$shortcode){
	if(\aw2_library::pre_actions('all',$atts,$content,$shortcode)==false)return;
		extract(\aw2_library::shortcode_atts( array(
			'config'=>'',
			'uid'=>'',
			'lapp_id'=>'',
			'msgno'=>''
			), $atts) );	
		
	$connection=gmail_connection($config);
	
	//$header = imap_fetch_overview($connection,$uid,FT_UID);	
	//$emails=json_decode(json_encode($header), True);
    	
	//echo $msgno;
	$structure = imap_fetchstructure($connection, $msgno);
	
	attachment($connection,$structure,$msgno,"yes",$lapp_id,$config);
	imap_close($connection);
	$result['status']="success";
	$result['message']="attachment saved successfully";
	return $result;
	
}

/*this will fetch single email data
	this function called from search and overview
*/
function single_email_data_bkp($connection,$config,$uid,$validation="yes"){  	
		
		$header = imap_fetch_overview($connection,$uid,FT_UID);
		$emails=json_decode(json_encode($header), True);
		
		if(empty($emails)){
			$result['status']="error";
		    $result['message']="no record found";
			return $result;
		}
		
		//this will validate only once during serach
		if($validation==="yes"){
			$valid_mail=$emails[0]['to'];
			$criteria= $config['criteria'];
			//echo $criteria;
			preg_match_all("/$criteria/",$valid_mail,$match);
			//print_r($match);
			if(count($match['found'])>0){			
				$emails[0]['object_id']=strstr($valid_mail,'@',true);		
			}		
			
		}
		
		
		$result['status']="success";
		$result['row']=$emails[0];	
		return $result;
		
}

function single_email_data($connection,$config,$uid,$validation="yes"){  	
		
		$header = imap_fetch_overview($connection,$uid,FT_UID);
		$emails=json_decode(json_encode($header), True);
		
		if(empty($emails)){
			$result['status']="error";
		    $result['message']="no record found";
			return $result;
		}
		
			
		if($validation==="yes"){

			$valid_mail=isset($emails[0]['to']) ? $emails[0]['to'] : '' ;
			$criteria= $config['criteria']; 	  	
			
			
			$header = imap_fetchheader($connection, $emails[0]['msgno']);
			preg_match("/$criteria/",$header,$match);
			if(isset($_COOKIE['vaibhav_test_123'])){
			echo $criteria;
			}
			
			//preg_match("/$criteria/",$valid_mail,$match);
		
			if(isset($match['found'])){
				$valid_mail=$match['found'];
				$emails[0]['object_id']=strtoupper(strstr($valid_mail,'@',true));
		
			}else{				
				//check in cc
				$header = imap_headerinfo($connection, $emails[0]['msgno']);				
				
				if(isset($header->ccaddress)){
					//echo "CC :- ".$header->ccaddress."</br>";
					preg_match("/$criteria/",$header->ccaddress,$match);
					if(isset($match['found'])){
						$valid_mail=$match['found'];
						$emails[0]['object_id']=strtoupper(strstr($valid_mail,'@',true));
						//echo "CC :- ".$emails[0]['object_id']."</br>";
					}
				}
			}
		}
		$result['status']="success";
	    $result['row']=$emails[0];
		return $result;
		
}



function gmail_connection($config){
	
	$username =$config['username'];	
	
	$password =$config['password'] ;
	 
	//Gmail host with folder
	$hostname = IMAPADDRESS . IMAPMAINBOX;
	 
	//Open the connection
	$connection = imap_open($hostname,$username,$password) or die('Cannot connect to Gmail: ' . imap_last_error());
	
	return $connection;
}


?>