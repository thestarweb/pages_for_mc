<?php
namespace minecraft;
class api_control{
	public function authserver_page($system,$a){

		switch($a){
			case 'authenticate'://登陆:/api/authserver/authenticate
				$post=get_post();
				$user=new user_server($system);
				if(isset($post->username)&&isset($post->password)&&$uinfo=$user->is_user($post->username,$post->password)){
					if($uinfo['isok']){
						$info=$user->get_info($uinfo['uid']);
						if(!$info){
							$all_info=$system->succ->call_fun('user','get_user_info',[$uinfo['uid']]);
							$info=[$user->creat_user_info($uinfo['uid'],'_'.$all_info['username'])];
						}
						$at=$uinfo['key'];//md5(uniqid(mt_rand(), true));
						$ct=isset($post->clientToken)?$post->clientToken:md5(time().rand(10,99));
						//var_dump(md5(time().rand(10,99)));exit;
						//$user->save_token($id,$at,$ct);
						echo json_encode([
							'accessToken'=>$at,
							'clientToken'=>$ct,
							'availableProfiles'=>$info,
							'selectedProfile'=>$info[0],
							'user'=>[
								'id'=>get_uuid($uinfo['uid']),
								'properties'=>[]
							]
						]);
						exit;
					}
				}
				header('HTTP/1.1 403 ForbiddenOperationException');
				$system->show_json([
					"error"=>"登录失败",
					"errorMessage"=>isset($uinfo)?$uinfo['info']:"参数错误"
				]);
				break;
		}
	}
	public function sessionserver_page($system,$a){
		$user=new user_server($system);
		switch ($a) {
			case 'session/minecraft/join'://进去服务器
				$post=get_post();
				if(isset($post->accessToken)&&isset($post->selectedProfile)&&isset($post->serverId)){
					$user->joinserver($post->accessToken,$post->selectedProfile,$post->serverId);
					header('HTTP/1.1 204 No Content');
				}
				break;
			case 'session/minecraft/hasJoined'://服务器验证
				if(isset($_GET['username'])&&isset($_GET['serverId'])){
					if($info=$user->has_joined($_GET['serverId'],$_GET['username'])){
						echo json_encode($user->to_data($info))	;
					}else{
						$s=file_get_contents('https://sessionserver.mojang.com/session/minecraft/hasJoined?username='.$_GET['username'].'&serverId='.$_GET['serverId']);
						if($s){
							echo $s;
						}else{
							header('HTTP/1.1 204 No Content');
						}
					}
				}
			
			default:

				//https://minecraft.thestarweb.sweb/api/sessionserver/session/minecraft/profile/0d7161b25dd6435f992ffd6e68d71252	
				if(strpos($a,'session/minecraft/profile/')===0){//获取资源
					$arr=explode('/', $a);
					if(isset($arr[3])){

						//优先正版资源
						//$ainfo=file_get_contents('https://sessionserver.mojang.com/session/minecraft/profile/'.$arr[3]);
						//$r=json_decode($ainfo);
						//if(!isset($r->error)){
						//	echo $ainfo;
						//	return;
						//}

						$uuid=$arr[3];
						$arr=$user->get_info_by_UUID($arr[3]);
						if($arr){
							$out=$user->to_data($arr[0]);
							echo json_encode($out);
						}else{
							echo file_get_contents('https://sessionserver.mojang.com/session/minecraft/profile/'.$uuid);
						}
					}
				}
				break;
		}
	}
	public function index_page($system,$a){
		echo json_encode([
			'meta'=>[
				'serverName'=>'Star Minecraft API',
				'implementationName'=>'star',
				'implementationVersion'=>'1.1.0',
				'feature.non_email_login'=>true
			],
			'skinDomains'=>[
				'.thestarweb.cn',
				'.thestarweb.sweb',
				'.minecraft.net'
			],
			'signaturePublickey'=>"-----BEGIN PUBLIC KEY-----\nMIGfMA0GCSqGSIb3DQEBAQUAA4GNADCBiQKBgQCrHLmoAeEBd7hroiDB2w2ZVFAh\nCNXS4juc3ZacgFnJqHfkaY8gnd8y7bHXEE0CQB8rE0np9GG2jPUCkAIAAWJBMRsG\nHYM2wrs0GHbmYaigzQWo1jaynf4FEkf+5h6VbiltoUxBbu5H0ueeZBaGD7PT+jkP\nQrgI0N3ZCjJeVE6lIQIDAQAB\n-----END PUBLIC KEY-----"
		],JSON_PRETTY_PRINT );
	}
}
