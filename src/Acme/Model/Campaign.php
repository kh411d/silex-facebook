<?php
namespace Acme\Model;
use Doctrine\DBAL\Connection;

Class Campaign {

  private $db;
	
	public function __construct(Connection $db){
	
		$this->db = $db;
	
	}
  
  
  public function current()
  {
   $sql = "SELECT * FROM slx_campaigns 
		   WHERE 
			  status = 'active' 
			  AND 
			  ( startdate <= '".date('Y-m-d H:i:s')."' AND enddate >= '".date('Y-m-d H:i:s')."' )  
			  ORDER BY startdate DESC 
			  LIMIT 1";
						  
		$q = $this->db->fetchAssoc($sql);						  
	    return 	$q ? $q : null;
  }

  public function add($data)
  {
	$ok = $this->db->insert('slx_campaigns',$data);
	
	if($ok){
		return 	$this->db->lastInsertId();
	}else{
		return false;
	}
  }
  
  public function update($data)
  {
	$ok = $this->db->update('slx_campaigns',$data,array('campaign_id'=>$data['campaign_id']));
	
	if($ok){
	 $gid = $data['campaign_id'];
     return $gid;
	}else{
		return false;
	}
  }
  
  

  
  public function remove($gid)
  {
		return $this->db->delete('slx_campaigns', array('campaign_id' => $gid));
  }
  
  public function retrieve($clauses = array() , $args = array())
	{
	   if(!is_array($args))
		  $args = array($args);

	if(!is_array($clauses))
	 parse_str($clauses,$clauses);

	$defaults = array('orderby' => 'campaign_id', 'order' => 'DESC', 'fields' => '*');
	$args = array_merge( $defaults, $args );
	extract($args, EXTR_SKIP);
	$order = ( 'desc' == strtolower($order) ) ? 'DESC' : 'ASC';
    
	$sql = "SELECT ";
	$sql .= $fields." ";
	$sql .= "FROM slx_campaigns ";

		foreach ($clauses as $key => $value){
		  if(is_array($value) && count($value)==1){
		   $where[] = $key." ".key($value)." ".current($value);
		  }else{
		   $where[] = $key." = ".$value;
		  }
		}
		
		if(isset($where) && count($where)>0)
			$sql .= " WHERE ".implode(" AND ",$where);
		
		$sql .= " ORDER BY ".$orderby." ".$order;

		if(@$limit_number && @$limit_offset)
			$sql .= " LIMIT ".$limit_offset.",".$limit_number;
		elseif(@$limit_number)
			$sql .= " LIMIT ".$limit_number;
  
	  return $this->db->fetchAll($sql);

	}
  
  public function getById($gid) 
  {
   $sql  = "SELECT slx_campaigns.* ";
   $sql .= "FROM slx_campaigns ";
   $sql .= "WHERE slx_campaigns.campaign_id = ".$gid;
   return $this->db->fetchAssoc($sql);
  }
  
  public function setStatus($gid,$status)
  {
   return $this->db->update('slx_campaigns', array('status'=>$status), array('campaign_id'=>$gid));
  }
}