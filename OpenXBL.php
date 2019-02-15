<?php
/*
----------------------------------
 ------  Created: 021519   ------
 ------    Austin Best	   ------
----------------------------------

OpenXBL, https://xbl.io
OpenXBL is an unofficial Xbox Live API
*/

//-- THIS SECTION WOULD GO IN A CONSTANTS FILE
define('OPENXBL_API_KEY', '{key_here}');

$OpenXBL_API = array('get_xuid' 		=> array('method' => 'get', 'endpoint' => 'https://xbl.io/api/v2/friends/search?gt=%s'),
					 'get_gamertag' 	=> array('method' => 'get', 'endpoint' => 'https://xbl.io/api/v2/account/%s'),
					 'get_friends' 		=> array('method' => 'get', 'endpoint' => 'https://xbl.io/api/v2/friends?xuid=%s'),
					 'get_presence' 	=> array('method' => 'get', 'endpoint' => 'https://xbl.io/api/v2/%s/presence'),
					 'send_message' 	=> array('method' => 'post', 'endpoint' => 'https://xbl.io/api/v2/conversations'),
					);

//-- THIS SECTION WOULD GO IN A FUNCTIONS FILE					
function process_openxbl_api($endpoint, $data)
{
	global $OpenXBL_API;
	
	$endpointData = $OpenXBL_API[$endpoint];
	$url = sprintf($endpointData['endpoint'], $data);
	
	$ch = curl_init();
	curl_setopt($ch, CURLOPT_URL, $url);
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
	curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
	curl_setopt($ch, CURLOPT_HTTPHEADER, array('X-Authorization:'. OPENXBL_API_KEY, 'Accept:application/json'));
	
	if ($endpointData['method'] == 'post')
	{
		curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
		curl_setopt($ch, CURLOPT_POST, 1);
	}
	
	$result = json_decode(curl_exec($ch), true);
	curl_close($ch);
	
	//-- RATE LIMIT EXCEEDED
	if ($result['message'] == 'Rate Limit Reached.')
	{
		unset($result);
		$result['rate_limit_error'] = true;		
	}
	
	return $result;
}

//-- THIS SECTION WOULD BE USED IN THE APPLICATION
$xuid 		= process_openxbl_api('get_xuid', '{gamertag}');
$gamertag 	= process_openxbl_api('get_gamertag', '{xuid}');
$friends 	= process_openxbl_api('get_friends', '{xuid}');
$presence 	= process_openxbl_api('get_presence', '{xuid,xuid,xuid}');
$message 	= process_openxbl_api('send_message', array('to' => '{gamertag}', 'message' => '{message}'));
