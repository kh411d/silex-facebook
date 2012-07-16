<?php
/**
 * CodeIgniter Campaign Controller
 *
 * Site front Controller
 *
 * @location    application/controller
 * @package     CodeIgniter
 * @author      Khalid Adisendjaja < kh411d@yahoo.com > 
 * @website		http://khalidadisendjaja.web.id
 */
 
Class Campaign extends CI_Controller {

	function __construct()
	{
	 parent::__construct();
	 $this->load->library('ezsql_mysql');
	 $this->load->model('campaign_m','campaign');
	 $this->load->model('form_m','form');
	 $this->load->model('media_m','media');
	 $this->load->model('page_m');
	}
	
	public function _remap($appID, $params = array())
	{
	  $method = isset($params[0]) ? $params[0] : 'home';	  
	  unset($params[0]);
	  
		if (method_exists($this, $method))
		{
			return call_user_func_array(array($this, $method), $params);
		}elseif(preg_match('/^[0-9a-zA-Z]+$/',$method)){
			return call_user_func_array(array($this, 'shorturl'), array( 1 => $method ));
		}
		show_404();
	}

	
	public function home()
	{
	  /** BEGIN REQUIRED VALIDATION **/
		$this->load->library('facebook');
	    $user = getAuthorizedUser(true);

		$isAuthorized = $user ? true : false;

	    if(!$campaign = $this->campaign->getActiveCampaign()){
			show_404();
		}
		/** END REQUIRED VALIDATION **/
		
		 $sr = $this->facebook->getSignedRequest();
		 $redirect_url = isset($sr['page']) ? $this->config->item('APP_FANPAGE') : $this->config->item('APP_CANVAS_PAGE')."/upload";
     	 if(isset($sr['page']) && !$sr['page']['liked']){
			redirect(menu_url('likepage').'?ref='.$redirect_url);
		 } 
		
		
		if($isAuthorized){
		 $redirect_url = menu_url('upload');
		}else{
		 $sr = $this->facebook->getSignedRequest();
		 $redirect_url = isset($sr['page']) ? $this->config->item('APP_FANPAGE') : $this->config->item('APP_CANVAS_PAGE')."/upload";
		}
		
		$this->load->view('site/home',array('campaign'=>$campaign,
										   'is_authorized' => $isAuthorized,
										   'redirectURL' => $redirect_url
										   ));	
	}
	
	public function addtopage()
	{
	  $this->notify->set_message('success', 'You\'re Fan Page successfuly setup. Please check your Fan Page Admin Panel');
	  redirect(site_url('admin/app/lists'));
	}
	
	public function upload()
	{
	
	 $this->load->library('facebook');
	 $this->load->model('customer_m','customer');
	

	
	 /** BEGIN REQUIRED VALIDATION **/
	 if(!$campaign = $this->campaign->getActiveCampaign()){
			show_404();
		}
	 
     if($campaign['on_judging']){
	    $data['campaign'] = $campaign;
		$data['message_title'] = "The Winner Announce Soon";
		$data['message_text'] = "Sorry! We are on Judging Time for The Campaign.";
		$this->load->view('site/campaign_notification',$data);
	    return;
	 }
	 
     if(!$campaign['on_upload']){
	    $data['campaign'] = $campaign;
		$data['message_title'] = "Campaign Upload End";
		$data['message_text'] = "Sorry! Upload submission for the campaign has just ended";
		$this->load->view('site/campaign_notification',$data);
		return;
	 }
	 

	 

	
     $sr = $this->facebook->getSignedRequest();
	 $redirect_url = isset($sr['page']) ? $this->config->item('APP_FANPAGE') : $this->config->item('APP_CANVAS_PAGE')."/upload";


	 if(!$user = getAuthorizedUser(true)){
		redirect(menu_url('authorize').'?ref='.$redirect_url);
	 }
	 

	 
	 if(!$isFan = user_isFan()){
	   redirect(menu_url('likepage').'?ref='.$redirect_url);
	 }	 
	 if(!$this->customer->isRegistered($campaign)){
	   redirect(menu_url('register'));	   
	 }
	 /** END REQUIRED VALIDATION **/
	 
	 
	 $form = $this->media->showUploadForm($campaign);
	 if($form == "success"){
		//redirect(menu_url('upload'));
		$media_type = $campaign['allowed_media_source'] == "file" ? "photo" : "video";
		$this->media->sendNotifyAdmin();
		if($campaign['media_has_approval']){
			$data['message_text'] = "Enjoy Your Smirnoff Ice while we're moderating your photo Check your email in a few minutes for further notification.  
<br/><br/>  
<span style='font-size:10px;text-align:center;'><em>Foto Kamu Telah Ter-upload
Nikmati Smirnoff Ice sambil menunggu foto kamu dimoderasi oleh admin. Cek juga email beberapa menit untuk melihat notifikasi dari kami.</em>
</span>";
	    }else{
			$data['message_text'] = "Thanks for participating, Your $media_type is now listed on the gallery.";
		}
			$data['message_title'] = "Successful <Br/> <span style='font-size:14px;'><em>Sukses</em></span>";
			$data['campaign'] = $campaign;
			$feed = array(
			   "name" => $campaign['title'],
			   "message" => 'Join Capture The Night!',
			   "caption" => "{*actor*} just posted my photo for ".$campaign['title'],
			   "description" => $campaign['description'],
			   "link" => $this->config->item('APP_CANVAS_PAGE')
			   );

			if(isset($campaign['asset_facebook']['logo_main'])){
				$feed['picture'] = $campaign['asset_facebook']['logo_main']['url'];
			}else{
				$feed['picture'] = base_url().'assets/site/img/logo-small.png';
			}			
			
			feedCreate($feed);
			
			$data['facebook_share_dialog'] = '<script>'.
											 '$(document).ready(function() {'.
											 'fbDialogFeed('.json_encode($feed).')'.
											 '});'.
											 '</script>';
		$this->load->view('site/upload_notification',$data);
	 }elseif($form == "error"){
		$this->notify->set_message( 'error', 'Sorry. Please Try Again.' );
		redirect(menu_url('upload'));
	 }else{
		$this->load->model('customer_m','customer');
		$this->load->library('facebook');
		$isAuthorized = $user ? true : false;
			
		$this->load->view('site/upload',array('campaign'=>$campaign,
											'html_form_upload' => $form
										   ));	
	 }									   
	}
	
	public function authorize()
	{
	  $this->load->library('facebook');
	  /** BEGIN REQUIRED VALIDATION **/
	  if(!$campaign = $this->campaign->getActiveCampaign()){
			show_404();
		}
	  /** END REQUIRED VALIDATION **/
	  
	  $redirectURL = urldecode($this->input->get_post('ref',true));
		
		$this->load->view('site/authorize',array(
											   'campaign'=>$campaign,
											   'fbpage_url' => $this->config->item('APP_FANPAGE'),
											   'redirectURL' => $redirectURL
											   ));	
	}
	
	public function likepage()
	{
	 $this->load->library('facebook');
	  /** BEGIN REQUIRED VALIDATION **/
	  if(!$campaign = $this->campaign->getActiveCampaign()){
			show_404();
		}
		
		$redirectURL = $this->input->get_post('ref',true);
		
/* 	 if(!$user = getAuthorizedUser(true)){
		redirect(menu_url('authorize').'?ref='.$redirectURL);
	 }	 */
	 /** END REQUIRED VALIDATION **/
	 
	 
		
		$this->load->view('site/likepage',array('campaign'=>$campaign,
											   'fbpage' => getFacebookPage(),
												'redirectURL' => $redirectURL
											   ));		
	}
	
	public function register()
	{
	 $this->load->library('facebook');
	 $this->load->model('customer_m','customer');
	 
	 /** BEGIN REQUIRED VALIDATION **/
	 if(!$campaign = $this->campaign->getActiveCampaign()){
			show_404();
	 }
	 
	 if($campaign['on_judging']){
	    $data['campaign'] = $campaign;
		$data['message_title'] = "The Winner Announce Soon";
		$data['message_description'] = "Sorry! We are on Judging Time for The Campaign.";
		$this->load->view('site/campaign_notification',$data);
		return;
	 }
	 
	 $sr = $this->facebook->getSignedRequest();
	 $redirect_url = isset($sr['page']) ? $this->config->item('APP_FANPAGE') : $this->config->item('APP_CANVAS_PAGE')."/register";
	
	 
	 if(!$user = getAuthorizedUser(true)){
		redirect(menu_url('authorize').'?ref='.$redirect_url);
	 }
	 
	 if(!$isFan = user_isFan()){
	   redirect(menu_url('likepage').'?ref='.$redirect_url);
	 }
	 
	 if($this->customer->isRegistered($campaign)){
	   redirect(menu_url('home'));	   
	 }
	 /** END REQUIRED VALIDATION **/
	 
	 
	 $form = $this->form->customer_register();
	
 	 if($form == "success"){
		redirect(menu_url('upload'));
	 }elseif($form == "error"){
		$this->notify->set_message( 'error', 'Sorry. Please Try Again.' );
		redirect(menu_url('register'));
	 }
	 
	 $this->load->view('site/register',array('campaign'=>$campaign,
										   'html_form_register' => $form
										   ));										
	}
	
	public function page($pageID)
	{
	 $this->load->library('facebook');
		/** BEGIN REQUIRED VALIDATION **/
/* 	 	if(!$campaign = $this->campaign->getActiveCampaign()){
			show_404();
		} */

		$sr = $this->facebook->getSignedRequest();
		$redirect_url = isset($sr['page']) ? $this->config->item('APP_FANPAGE') : $this->config->item('APP_CANVAS_PAGE')."/register";		
		
		if(!$user = getAuthorizedUser(true)){
		 redirect(menu_url('authorize').'?ref='.$redirect_url);
	    }
		/** END REQUIRED VALIDATION **/
		
		
		$this->load->model('page_m');
		if($page = $this->page_m->detailPage($pageID)){
		    if(!$page['page_facebook']) show_404();
			if(date('Y-m-d H:i:s') < $page['page_publish_date'] || $page['page_status'] == 'draft'){
				show_404();
			}else{
			  //$page['campaign'] = $campaign;
			  $page['campaign'] = $this->campaign->detailCampaign($page['GID']);
			  $this->load->view('site/page',$page);	
			}
		}else{
			show_404();
		}
		
	}
	 
	 public function winner()
	 {
	  $this->load->library('facebook');
	  /** BEGIN REQUIRED VALIDATION **/
	  	 $sr = $this->facebook->getSignedRequest();
		$redirect_url = isset($sr['page']) ? $this->config->item('APP_FANPAGE') : $this->config->item('APP_CANVAS_PAGE')."/winner";
	   	if(!$campaign = $this->campaign->getActiveCampaign()){
			show_404();
		}
		
		if(!$user = getAuthorizedUser(true)){
			redirect(menu_url('authorize').'?ref='.$redirect_url);
		}	 
		/** END REQUIRED VALIDATION **/

		$data = array();
		if($campaign['on_judging'] && $campaign['winner_announced']){
		  //Get Winner
		  if($media = $this->media->retrieveMedia(array('campaign_media.GID'=>$campaign['GID'],'campaign_media.media_winner' => 1))){
			$data['media'] = $media;
		  }
		}else{
			show_404();
		}
		$data['campaign'] = $campaign;
		
		$this->load->view('site/winner',$data);
	 }
	  
	  public function votesecure()
	  {
	   $this->load->library('facebook');
	  
	  /** BEGIN REQUIRED VALIDATION **/
	  if(!$media = $this->session->userdata('media_secure_vote')){
		show_404();
	  }
	  //dg($media);
	   $sr = $this->facebook->getSignedRequest();
	   $redirect_url = isset($sr['page']) ? $this->config->item('APP_FANPAGE') : $this->config->item('APP_CANVAS_PAGE')."/media?m=".$media['media_id'];

	   	if(!$user = getAuthorizedUser(true)){
		  redirect(menu_url('authorize').'?ref='.$redirect_url);
	    }
		
		if(!$isFan = user_isFan()){
			redirect(menu_url('likepage').'?ref='.$redirect_url);
		}
		
		$campaign = $this->campaign->detailCampaign($media['GID']);
	  
		
		$form = $this->form->vote_form_captcha($media);
		
		if($form == "vote_ok"){
			$this->session->unset_userdata('media_secure_vote');
			redirect(menu_url('media')."?m=".$media['media_id']);
		}
		$data = array('form' => $form,'campaign' => $campaign);
		$this->load->view('site/votesecure',$data);
	  }

	  
	  public function media($media_id = null)
	  { 
	   $this->load->library('facebook');

	  /** BEGIN REQUIRED VALIDATION **/
		if(!$media_id){
			if(!$media_id = addslashes($this->input->get('m', TRUE))){
			  show_404();
			}
		}
		
		//if(!is_int($media_id))show_404();

		$rowMedia = $this->media->detailMedia($media_id);
	   $sr = $this->facebook->getSignedRequest();
	   $redirect_url = isset($sr['page']) ? $this->config->item('APP_FANPAGE') : $this->config->item('APP_CANVAS_PAGE')."/media?m=$media_id";

	   	if(!$user = getAuthorizedUser(true)){
	 /*  $campaign = $this->campaign->detailCampaign($rowMedia['GID']);
		 $fblike_href = menu_url('media').'/?m='.$rowMedia['media_id'];
		 $meta = $this->media->setOpenGraphMeta(array(
													 'title' => $campaign['title'],
													 'type' => 'activity',
													 'image' => $rowMedia['media_thumb_url'],
													 'url' => $fblike_href,
													 'site_name' => $campaign['title']
													));
		 registerMetaTags($meta); */
		  //redirect(menu_url('authorize').'?ref='.$redirect_url);
	    }

		
		if(!$isFan = user_isFan()){
			$gofansURL = menu_url('likepage').'?ref='.$redirect_url;
		}

		/** END REQUIRED VALIDATION **/
		
	    $this->load->model('setting_m');
		
		if($rowMedia){
			$campaign = $this->campaign->detailCampaign($rowMedia['GID']);
			//if campaign out of date
			 $fblike_href = menu_url('media').'/?m='.$rowMedia['media_id'];
			$campaign_status = $this->campaign->getStatus($campaign);
				$meta = $this->media->setOpenGraphMeta(array(
															 'title' => $campaign['title'],
															 'type' => 'activity',
															 'image' => $rowMedia['media_thumb_url'],
															 'url' => $fblike_href,
															 'site_name' => $campaign['title']
															));
				registerMetaTags($meta);
			
			if($campaign_status['is_off'] || $rowMedia['media_status'] == 'pending' || $rowMedia['media_status'] == 'banned'){
				$rowMedia['media_container'] = $this->media->showMedia($rowMedia,false);
				$campaign['media_preview'] = true;
				$this->load->view('site/media_preview',array('campaign'=>$campaign,'media' => $rowMedia));	
			}else{
			   
				
				$plugin_switch = array();
				$plugin_switch[] = $user && $campaign['media_has_vote'] && $campaign_status['on_vote'] ? 'vote' : null;
				$plugin_switch[] = $campaign['media_has_fblike'] ? 'fblike' : null;
				$plugin_switch[] = $campaign['media_has_fbcomment'] ? 'fbcomment' : null;
				
				$plugin = $this->media->getPlugin($rowMedia,$plugin_switch);
				if(!$user){
				$plugin['votebutton'] = authorizeButton('Login to Vote',$redirect_url);
				}elseif(!$isFan){
				$plugin['votebutton'] = "Only Fans Of Smirnoff Indonesia can Vote! <a href='".$gofansURL."'>Click Here</a>";
				}
				
				$rowMedia['media_container'] = $this->media->showMedia($rowMedia,false);

				$this->load->view('site/media',array('campaign'=>$campaign,'plugin'=>$plugin,'media' => $rowMedia));										
			}
		}else{
		 show_404(); 
		}
	  }
	  
	  public function gallery()
	  {	  
	  $this->load->library('facebook');
	  
	   require_once 'Pager/Sliding.php';
	   /** BEGIN REQUIRED VALIDATION **/
	   if(!$active_campaign = $this->campaign->getActiveCampaign()){
			show_404();
	   }
	   
	   $sr = $this->facebook->getSignedRequest();
	   $redirect_url = isset($sr['page']) ? $this->config->item('APP_FANPAGE') : $this->config->item('APP_CANVAS_PAGE')."/gallery";

	   
	   if(!$user = getAuthorizedUser(true)){
		redirect(menu_url('authorize').'?ref='.$redirect_url);
	   }
	   /** END REQUIRED VALIDATION **/
	 
    	 $userMedia = $this->media->mediaByUID($user['id'],$active_campaign['GID'],'active');
		 $randMedia = $this->media->mediaByRandom($active_campaign['GID'],'active');
		 

	   
	   $sql_filter = "WHERE campaign_media.media_status = 'active' AND campaign_media.GID = ".$active_campaign['GID'];
	   
	   	if($bysearch = $this->input->get_post('searchby',true)){
		  if($media_ids = $this->media->getMediaIdsByCustomer($active_campaign,$bysearch)){
			  $clauses['campaign_media.media_id'] = $media_ids;
			  $sql_filter .= ' AND campaign_media.media_id IN ('.implode( ",", $media_ids).') ';
		  }else{
			$clauses['campaign_media.media_id'] = 0;
		  }
		} 
	   
	   
	   $sumPerCampaign = $this->ezsql_mysql->get_var("SELECT COUNT(*) FROM campaign_media ".$sql_filter);
       
	   $orderby = 'campaign_media.media_id';
		$order = 'DESC';
		
		if($byorder = $this->input->get_post('orderby', TRUE)){		
					switch($byorder){
						case "mostvote" : 	$orderby = "campaign_media.media_vote_total"; 
											$order = "DESC"; break;
						case "latest" :  	   $orderby = 'campaign_media.media_id';
											$order = 'DESC'; break;
					}
		}	


	   

	   //$config['path'] = APP_ADMIN_URL;
	   	$config['curPageLinkClassName'] = "active";
		$config['prevImg'] = "&laquo; Previous";
		$config['nextImg'] = "Next &raquo;";
		$config['totalItems'] = $sumPerCampaign;
		$config['path'] = menu_url('gallery');
		$config['perPage'] = 9; 
		$config['urlVar'] = ($this->input->post('orderby') ? 'orderby='.$this->input->post('orderby').'&' : '').
							($this->input->post('searchby') ? 'searchby='.$this->input->post('searchby').'&' : '').
							'pageID';
		$pager = new Pager_Sliding($config);
		$pageID = $this->input->get_post('pageID',true) ? $this->input->get_post('pageID',true) : 1;
		$links = $pager->getLinks($pageID);
		list($from, $to) = $pager->getOffsetByPageId();
		
		$clauses['campaign_media.media_status'] = 'active';
		$clauses['campaign_media.GID'] = $active_campaign['GID'];
		$args = array('orderby'=>$orderby,'order'=>$order,'limit_number' => $config['perPage'],'limit_offset' => --$from);

		$rowsMedia = $this->media->retrieveMedia($clauses,$args);
		$this->load->view('site/gallery',array('campaign'=>$active_campaign,
												'media' => $rowsMedia,
												'user_media' => $userMedia ? $userMedia : null,
												'random_media' => $randMedia ? $randMedia : null,
												'pagination'=>$links));	
	  }
  
	  public function rules()
	  {
	    $this->load->library('facebook');
	   
	   /** BEGIN REQUIRED VALIDATION **/
	    if(!$campaign = $this->campaign->getActiveCampaign()){
			show_404();
		}
		$sr = $this->facebook->getSignedRequest();
		$redirect_url = isset($sr['page']) ? $this->config->item('APP_FANPAGE') : $this->config->item('APP_CANVAS_PAGE')."/rules";

	    if(!$user = getAuthorizedUser(true)){
			redirect(menu_url('authorize').'?ref='.$redirect_url);
	    }
		/** END REQUIRED VALIDATION **/
		
		$this->load->view('site/rules',array('campaign'=>$campaign,
										'rules' => $campaign['campaign_rules']
		));	
	  }
	  
	  public function shorturl($keyword)
	  {
	    $this->load->library('shorturl');
		//check for media, prefixed with 'm'
		$this->shorturl->setPrefix('m');
		if($media_id = $this->shorturl->keywordToInt($keyword)){
			$this->media($media_id);
		}else{
		  //Check for  Pages, prefixed with 'p'
		  $this->shorturl->setPrefix('p');
		  if($page_id = $this->shorturl->keywordToInt($keyword)){
			$this->page($page_id);
		  }
		}
	  }
	  
	  public function voteabusetest()
	  {
	   error_reporting(E_ALL);
		$this->load->library('facebook');
	    $this->load->library('ezsql_mysql');
		$this->db = $this->ezsql_mysql;
		$rows = $this->db->get_results("SELECT * FROM campaign_media_vote WHERE media_id = 106",'ARRAY_A');

	   foreach($rows as $row){
	     	//if($content = $this->facebook->api('/'.$row['uid'].'?fields=id,name,email,birthday,link,first_name,last_name,username,gender&access_token=422327604449372|I_QnniZFD_wLbob154WY1Kz6_UI')){
			if($content = file_get_contents_curl('https://graph.facebook.com/'.$row['uid'].'?access_token=422327604449372|I_QnniZFD_wLbob154WY1Kz6_UI')){
			//dg($content);
			$u = json_decode($content,true);
			dg($u);
			}else{
				echo $row['uid']."  Unidentified";
			}  
		 echo "<br/>";	
	   }
	   exit;

	  }

}