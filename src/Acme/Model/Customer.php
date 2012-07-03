<?php
namespace Acme\Model;
use Doctrine\DBAL\Connection;

 Class Customer {
	public $db;
	
	public function __construct(Connection $db){
	
		$this->db = $db;
	
	}
	
	public function get_all(){
	
		$sql = "SELECT * FROM slx_customer_fbrel";
		$post = $this->db->fetchAssoc($sql);
		return $post;
		
	}
 
 }