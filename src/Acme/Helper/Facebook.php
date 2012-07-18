<?php
namespace Acme\Helper;
 
 Class Facebook {
 protected   $facebook;
 protected   $config;
 
 public   function __construct(\Silex\Application $app){
   $this->facebook = $app['facebook'];
   $this->config['app_id'] = $app['facebook.app_id'];
   $this->config['secret'] = $app['facebook.secret'];
   $this->config['permissions'] = $app['facebook.permissions'];
 } 
 
 public    function fetchRequests($input_request_ids){
   $request_ids = explode(',', $input_request_ids);
   $data = array();
   foreach ($request_ids as $request_id)
   {
      $full_request_id = $request_id . '_' . $user_id;
      try { $data[] = $this->facebook->api("/$full_request_id");}
      catch (Exception $e) {}
   }
   return $data;
 }

 public    function deleteRequests($input_request_ids){
   $request_ids = explode(',', $input_request_ids);
   $user_id = $this->facebook->getUser();
   foreach ($request_ids as $request_id)
   {
      $full_request_id = $request_id . '_' . $user_id;
      try {$facebook->api("/$full_request_id",'DELETE');}
      catch (Exception $e) {}
   }
   return;
 }
 
 /*
*
* Feed array
* Array('message'=>'','link'=>'',picture'=>'','name'=>'','caption'=>'','description'=>'','actions'=>'')
* message and link are required.
*/
 public    function feedCreate(Array $feed){
   if(!isset($feed['message']) || !isset($feed['link'])) return false;

   try {
   $this->facebook->api("me/feed","post",$feed);
   return true;
   }catch (Exception $e) { return false;}
 }
 
 public    function getAppAccessToken(){
    $parameter = array('client_id' => $this->config['app_id'],
						'client_secret' => $this->config['secret'],
						'grant_type' => 'client_credentials');
	try{
		$request = graph_request('/oauth/access_token', 'GET',$parameter,true,false);
		parse_str($request);
	} catch (Exception $e){ return NULL; }
	
	$request = $request ? $access_token : NULL;
	return $request;
 }
 

 
 public    function isFan(){

   $sr = $this->facebook->getSignedRequest();
   if(!$sr['page']['liked'])return false;
   return true;
 }
 
 public    function user_isFan($pageID){
   $isFan = false;
   
   if(!$this->facebook->getUser()) return false;
  
    try{
		$isFan = $this->facebook->api(array(
			"method" => "pages.isFan",
			"page_id" => $pageID,
			"uid" => $this->facebook->getUser()
		));
	} catch (Exception $e){ return false; }

	return $isFan === TRUE ? true : false;
 }
 
 /*
*
* http://developers.facebook.com/docs/reference/api/application/
*
*
*/
 public   function getAppDetail($appid,$app_accesstoken,$fields = array()){
 
  if(!$fields){
	$fields = array('id','name','link','namespace','logo_url','restrictions',
	'app_domains','canvas_url','contact_email','creator_uid','page_tab_default_name',
	'page_tab_url','privacy_policy_url','secure_canvas_url','secure_page_tab_url',
	'website_url');
  }
 
   $parameter = array( 'fields' => implode(',',$fields),'access_token' => $app_accesstoken );
	try{
		$request = graph_request('/'.$appid, 'GET',$parameter,true,true);
	}catch(Exception $e){
		return null;
	}
	return $request;
 }

 public   function getAppByIDS($appid,$appsecret){
   if($access_token = getAppAccessToken(array('app_id' => $appid,'app_secret'=> $appsecret))){
     return getAppDetail($appid,$access_token);
   }else{
  return null;
   }
 }
	/*
	location = Restriction based on location, such as 'DE' for apps restricted to Germany
	age = Minimum age restriction
	age_distribution = Restriction based on an age range
	id = App ID read-only field.
	type = Always application for apps; read-only field.
	*/
 public   function setAppRestriction($appid,$app_accesstoken,$fields = array()){
	  if(!$fields){
	$fields = array("age_distribution"=>"21+");
	  }
		$parameter = array( 'restrictions' => json_encode($fields),
	'access_token' => $app_accesstoken );
	  try{
	$request = graph_request('/'.$appid, 'POST',$parameter,true,false);
	}catch(Exception $e){
	return null;
	}
	//$request = file_get_contents("https://graph.facebook.com/$appid?".http_build_query($parameter, null, '&'));
	return $request;
 }

 public   function appToPage_dialog_url($appid,$redirecturl){
  return "http://www.facebook.com/dialog/pagetab?app_id=$appid&next=$redirecturl";
 }
 
 public   function graph_request($path,$method = "POST",$args = array(),$ssl = true,$json_decode = true,$debug=false){
   $ch = curl_init();
   $domain = "graph.facebook.com";
   $method = strtoupper($method);
   $url = $ssl ? "https://".$domain.$path : "http://".$domain.$path;
   
    if($method == 'POST'){
		curl_setopt($ch, CURLOPT_POST, true);
	}elseif($method == 'GET'){
		curl_setopt($ch, CURLOPT_HTTPGET, true);
	}elseif($method == 'DELETE'){
		curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "DELETE");
		curl_setopt($ch, CURLOPT_HTTPGET, true);
	}

    if($args && $method == 'POST')
		curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($args, null, '&'));
	elseif($args && $method == 'GET')
		$url .= '?'.http_build_query($args, null, '&');

	curl_setopt($ch, CURLOPT_HEADER, false);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 2);
   
	curl_setopt($ch, CURLOPT_URL, $url);

	$result = curl_exec($ch);
	if ($result === false) {
			curl_error($ch);
			
			curl_close($ch);	
		return false;
    }

	curl_close($ch);
	return $json_decode ? json_decode($result,true) : $result;
   }
 
 
 public   function isAppUser($uid)
 {
  $facebook = $this->facebook;
      try{
		return $facebook->api(array('method'=>'users.isAppUser','uid'=>$uid));
	  } catch (Exception $e){
		return false;//Got an exception of invalid OAUTH 2.0 token
	  }
 }
 
 public   function callback_check_fbuser($fb_uid){
	$uid = $fb_uid;
	
	if($content = file_get_contents_curl('http://graph.facebook.com/'.$uid)){
		$u = json_decode($content,true);
		return isset($u['id']) ? true : false;
	}else{
		return false;
	}
 }
 
 
 public   function getFacebookUser($uid){
	$content = file_get_contents_curl('http://graph.facebook.com/'.$uid);
	return json_decode($content);
 }
 
 public   function getAuthorizedUser($permissions = true){

  $profile = null;
  if(!$this->facebook->getUser()) return null;
    try {
		$profile = $this->facebook->api('/me?fields=id,name,email,link,first_name,last_name,username,gender');
		if(isset($profile['birthday'])){
			$birthday_date = DateTime::createFromFormat('m/d/Y', $profile['birthday']);
			$now_date = new DateTime(date('Y-m-d'));
			$profile['age'] = (int) $birthday_date->diff($now_date)->format('%y');
		}

		if($permissions){
			$APP_EXT_PERMISSIONS = explode(',',$this->config['permissions']);
			if($data = $this->facebook->api('/me/permissions')){
				$scopes = $data['data'][0];
				foreach($APP_EXT_PERMISSIONS as $PERMS){
					if(isset($scopes[$PERMS]) && $scopes[$PERMS] == 1) {
						continue;
					}else{
						$profile = null;
					break;
					}
				}
			}
			if($profile) $profile['scope'] = $scopes;
		}
 } catch (Exception $e) {}
  return $profile;
 }
 


  public   function authorizeButton($text = 'Click here to Authorize',$redirectURL = null){
	$redirectURL = $redirectURL ? "'".$redirectURL."'" : null;
    return "<a onclick=\"fbDialogLogin(".$redirectURL."); return false;\" data-ajax=\"false\" class=\"fb_button fb_button_medium\"><span class=\"fb_button_text\">".$text."</span></a>";
  }
 
 public   function fblike($href,$attr = "show_faces='false' width='430' font=''")
  {
     return "<fb:like href='$href' $attr ></fb:like>";
  }
  
 public   function fbcomment($href,$attr = "colorscheme='light' width='460' num_posts='5'")
  {
   return "<fb:comments href='$href' $attr ></fb:comments>";
  }
  }