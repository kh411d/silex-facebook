<?php
namespace Acme\Model;
use Doctrine\DBAL\Connection;

Class CustomerItem {

  private $db;
	
	public function __construct(Connection $db){
	
		$this->db = $db;
	
	}
  

  public function add($data)
  {
    return $this->db->insert('slx_customer_items',$data);
  }
  
  public function update($data)
  {
	$ok = $this->db->update('slx_customer_items',$data,array('item_id'=>$data['item_id']));
	
	if($ok){
	 $gid = $data['item_id'];
     return $gid;
	}else{
		return false;
	}
  }
  
  public function remove($gid)
  {
	return $this->db->delete('slx_customer_items', array('item_id' => $gid));
  }
  
  public function retrieve($clauses = array() , $args = array())
	{
	   if(!is_array($args))
		  $args = array($args);

	if(!is_array($clauses))
	 parse_str($clauses,$clauses);

	$defaults = array('orderby' => 'item_id', 'order' => 'DESC', 'fields' => '*');
	$args = array_merge( $defaults, $args );
	extract($args, EXTR_SKIP);
	$order = ( 'desc' == strtolower($order) ) ? 'DESC' : 'ASC';
    
	$sql = "SELECT ";
	$sql .= $fields." ";
	$sql .= "FROM slx_customer_items ";
	
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
   $sql  = "SELECT slx_customer_items.* ";
   $sql .= "FROM slx_customer_items ";
   $sql .= "WHERE slx_customer_items.item_id = ".$gid;
   return $this->db->fetchAssoc($sql);
  }
  

}