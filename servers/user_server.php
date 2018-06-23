<?php
class user_server{
	private $system;
	public function __construct($system){
		$this->system=$system;
	}
	public function is_user($username,$password){
		if($username=='2@star.star'&&$password=='123') return 1;
		if($username=='1@1.com'&&$password=='123') return 2;
		return false;
	}
	public function get_info($uid){
		$uid+=0;
		$res=$this->system->db()->exec('SELECT * FROM `@%_user` WHERE `id`='.$uid);
		$ret=[];
		foreach($res as $v){
			$ret[]=$this->to_data($v);
		}
		return $ret;
	}
	public function get_info_by_UUID($uuid){
		return $this->system->db()->u_exec('SELECT * FROM `@%_user` WHERE `uuid`=?',[$uuid]);
	}
	public function to_data($data){
		if(!$data) return;
		$out=[
			'id'=>$data['uuid'],
			'name'=>$data['name']
		];
		if($data['skin']){
			$t=[
				'timestamp'=>time(),
				'profileId'=>$data['uuid'],
				"profileName"=>$data['name'],
				//"signatureRequired": true, // Only present if ?unsigned=false is appended to url
				'textures'=>[
					'SKIN'=>[
						'url'=>$data['skin']
					],
					/*"CAPE": [
						"url": "<player cape URL>"
					]*/
				]
			];

			$out['properties']=[ 
				[
				'name'=>'textures',
				'value'=>base64_encode(json_encode($t)),
				//'signature'=>'<base64 string; signed data using Yggdrasil\'s private key>' // Only provided if ?unsigned=false is appended to url
				]
			];
		}
		return $out;
	}
	public function save_token($id,$at,$ct){
		$id+=0;
		//$this->system->db()->exec('DELETE FROM `@%_token` WHERE `id`='.$id);
		$this->system->db()->u_exec('INSERT INTO `@%_token`(`id`,`AT`,`CT`,`time`) VALUE(?,?,?,?)',[$id,$at,$ct,time()]);
	}
	public function joinserver($at,$uuid,$serverid){
		$a=$this->system->db()->u_exec('SELECT `id` FROM `@%_token` WHERE `AT`=?',[$at]);
		if($a){
			$this->system->db()->u_exec('INSERT INTO `@%_join_server`(`serverid`,`uuid`,`uid`,`ip`,`time`) VALUE(?,?,?,?,?)',[$serverid,$uuid,$a[0]['id'],$this->system->uip(),time()]);
		}
		
	}
	public function has_joined($serverid,$name){
		$a=$this->system->db()->u_exec('SELECT `uuid`,`uid` FROM `@%_join_server` WHERE `serverid`=?',[$serverid]);
		if($a){
			$res=$this->system->db()->u_exec('SELECT * FROM `@%_user` WHERE `uuid`=?',[$a[0]['uuid']]);
			if($res&&$res[0]['id']==$a[0]['uid']&&$res[0]['name']==$name){
				return $res[0];
			}
		}
	}
}