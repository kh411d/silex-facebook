<?php
namespace Acme\Model;
use Doctrine\DBAL\Connection;

Class Page {

  private $db;
  public $error = array();
  
  function __construct(Connection $db)
  {
       $this->db = $db;
  }

  public function add($page_data)
  {
	$ok = $this->db->insert('slx_campaign_page',$page_data);
	
	if($ok){
	 $id = $this->db->lastInsertId();
	 return $id;
	}else{
		return false;
	}
  }
  
  public function update($data)
  {
	$ok = $this->db->update('slx_campaign_page',$data,array('page_id'=>$data['page_id']));
	
	if($ok){
	 $page_id = $data['page_id'];
     return $page_id;
	}else{
	  return false;
	}
  }
    
  
  public function remove($page_id)
  {
	$deleted = $this->db->query("DELETE FROM slx_campaign_page WHERE page_id = ".$page_id);
	if($deleted){
		return true;
	}
	return false;
  }
  
  public function retrieve($clauses = array() , $args = array())
	{
	   if(!is_array($args))
		  $args = array($args);

		if(!is_array($clauses))
		 parse_str($clauses,$clauses);

		$defaults = array('orderby' => 'page_id', 'order' => 'DESC', 'fields' => 'slx_campaign_page.*,campaign_group.title,campaign_group.APP_APPLICATION_ID');
		$args = array_merge( $defaults, $args );
		extract($args, EXTR_SKIP);
		$order = ( 'desc' == strtolower($order) ) ? 'DESC' : 'ASC';
		
		$sql = "SELECT ";
		$sql .= $fields." ";
		$sql .= "FROM slx_campaign_page INNER JOIN campaign_group ON slx_campaign_page.GID = campaign_group.GID ";

		foreach ($clauses as $key => $value){
		  if(is_array($value)){
		   $where[] = $key." IN (".implode(",",$value).") ";
		  }else{
		   $where[] = $key." = ".$value;
		  }
		}
		
		if(isset($where) && count($where)>0)
			$sql .= " WHERE ".implode(" AND ",$where);
		
		$sql .= " ORDER BY ".$orderby." ".$order;

		if(isset($limit_number) && isset($limit_offset))
			$sql .= " LIMIT ".$limit_offset.",".$limit_number;
		elseif(isset($limit_number))
			$sql .= " LIMIT ".$limit_number;

	  return $this->db->get_results($sql,'ARRAY_A');

	}
  
  public function getById($page_id) 
  {
   $sql  = "SELECT slx_campaign_page.* ";
   $sql .= "FROM slx_campaign_page ";
   $sql .= "WHERE slx_campaign_page.page_id = ".$page_id;
   return $this->db->get_row($sql,'ARRAY_A');
  }
  
  public function setStatus($page_id,$status)
  {
   return $this->db->update('slx_campaign_page', array('status'=>$status), array('page_id'=>$page_id));
  }
}