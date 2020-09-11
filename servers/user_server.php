<?php
namespace minecraft;
class user_server{
	private $system;
	public function __construct($system){
		$this->system=$system;
		$_SERVER['HTTP_USER_AGENT']="Minecrat Api";
	}
	public function is_user($username,$password){
		if(substr($username,-1)=='@'){
			$username=substr($username, 0,-1);
		}
		return $this->system->succ->call_fun('login','try_to',['name',$username,$password]);
	}
	public function creat_user_info($uid,$name){
		$uuid=$this->system->server('uuid')->get_offline_player_uuid($name);
		$this->system->db()->u_exec('INSERT INTO `@%_user`(`id`,`uuid`,`name`,`skin`) VALUES(?,?,?,?)',[$uid,$uuid,$name,'']);
		return $this->to_data([
			'id'=>$uid,
			'uuid'=>$uuid,
			'name'=>$name,
			'skin'=>''
		]);
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
		//$a=$this->system->db()->u_exec('SELECT `id` FROM `@%_token` WHERE `AT`=?',[$at]);
		$user=$this->system->succ->call_fun('login','is_login_token',[$at,$_SERVER['HTTP_USER_AGENT']]);
		if($user){
			$this->system->db()->u_exec('INSERT INTO `@%_join_server`(`serverid`,`uuid`,`uid`,`ip`,`time`) VALUE(?,?,?,?,?)',[$serverid,$uuid,$user['uid'],$this->system->uip(),time()]);
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
