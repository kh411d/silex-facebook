<?php
namespace Acme\Model;
use Doctrine\DBAL\Connection;

Class Customer {

  private $db;
	
	public function __construct(Connection $db){
	
		$this->db = $db;
	
	}
  

  public function add($data)
  {
	try{
		$this->db->beginTransaction();
		$sql = "INSERT INTO slx_customer_profile (`name`,`email`,`phone`,`address`,`regdate`) VALUES ('{$data['name']}','{$data['email']}','{$data['phone']}','{$data['address']}','".date('Y-m-d H:i:s')."')";
		$sth = $this->db->prepare($sql);
		$sth->execute($data);
		if($customer_id = $this->db->lastInsertId()){
			$sql = "INSERT INTO slx_customer_fbrel (`customer_id`,`fb_uid`) VALUES ($customer_id,{$data['fb_uid']})";
			$sth = $this->db->prepare($sql);
			$sth->execute($data);			
			$this->db->commit();
			return true;
		}else{
			throw New \Exception('Something went wrong!');
		}
	} catch (\Exception $e) {
		$this->db->rollback();
		return false;
	}
  }
  
  
  public function isRegistered($fb_uid)
  {
   $sql  = "SELECT slx_customer_fbrel.customer_id ";
   $sql .= "FROM slx_customer_profile ";
   $sql .= "INNER JOIN slx_customer_fbrel ON slx_customer_profile.customer_id = slx_customer_fbrel. customer_id ";
   $sql .= "WHERE slx_customer_fbrel.fb_uid = ?";
   $customer_id = $this->db->fetchColumn($sql, array($fb_uid), 0);

  	return $customer_id;
   }
  
  public function update($data)
  {
	$ok = $this->db->update('slx_customer_profile',$data,array('customer_id'=>$data['customer_id']));
	
	if($ok){
	 $gid = $data['customer_id'];
     return $gid;
	}else{
		return false;
	}
  }
  
  public function updateFbAuth($fb_uid,$access_token)
  {
    	    $sql = "INSERT INTO slx_facebook_auth (`fb_uid`,`access_token`) VALUES ($fb_uid,$access_token) ON DUPLICATE KEY UPDATE fb_uid=VALUES(fb_uid),access_token=VALUES(access_token)";
			$sth = $this->db->prepare($sql);
			$ok = $sth->execute($data);
	
	if($ok){
	 return array($fb_uid,$access_token);
	}else{
		return false;
	}
  }  

  
  public function remove($gid)
  {
	try{
		$this->db->beginTransaction();
		$sth = $this->db->prepare("DELETE slx_customer_profile WHERE customer_id = $gid");
		$sth->execute($data);
		$sth = $this->db->prepare("DELETE slx_customer_fbrel WHERE customer_id = $gid");
		$sth->execute($data);		
		if(!$result = $this->db->commit()){
				throw New \Exception('Something went wrong!');
		}
		return true;	
	} catch (\Exception $e) {
	    $this->db->rollback();
		return false;
	}
  }
  
  public function retrieve($clauses = array() , $args = array())
	{
	   if(!is_array($args))
		  $args = array($args);

	if(!is_array($clauses))
	 parse_str($clauses,$clauses);

	$defaults = array('orderby' => 'slx_customer_profile.customer_id', 'order' => 'DESC', 'fields' => 'slx_customer_profile.*, slx_customer_fbrel.fb_uid');
	$args = array_merge( $defaults, $args );
	extract($args, EXTR_SKIP);
	$order = ( 'desc' == strtolower($order) ) ? 'DESC' : 'ASC';
    
	$sql = "SELECT ";
	$sql .= $fields." ";
	$sql .= "FROM slx_customer_profile ";
	$sql .= "INNER JOIN slx_customer_fbrel ON slx_customer_profile.customer_id = slx_customer_fbrel. customer_id ";

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
  
  public function getById($gid,$getBy = 'cid') 
  {
   $sql  = "SELECT slx_customer_profile.*,slx_customer_fbrel.fb_uid ";
   $sql .= "FROM slx_customer_profile ";
   $sql .= "INNER JOIN slx_customer_fbrel ON slx_customer_profile.customer_id = slx_customer_fbrel. customer_id ";
   if($getBy == 'cid')
	$sql .= "WHERE slx_customer_profile.customer_id = ".$gid;
   elseif($getBy == 'fbuid')
    $sql .= "WHERE slx_customer_fbrel.fb_uid = ".$gid;
	
   return $this->db->fetchAssoc($sql);
  }
  
  

}