<?php
namespace minecraft;
class uuid_server{
	//from https://gist.github.com/games647/2b6a00a8fc21fd3b88375f03c9e2e603
	public function get_offline_player_uuid($username) {
		//extracted from the java code:
		//new GameProfile(UUID.nameUUIDFromBytes(("OfflinePlayer:" + name).getBytes(Charsets.UTF_8)), name));
		$data = hex2bin(md5("OfflinePlayer:" . $username));
		//set the version to 3 -> Name based md5 hash
		$data[6] = chr(ord($data[6]) & 0x0f | 0x30);
		//IETF variant
		$data[8] = chr(ord($data[8]) & 0x3f | 0x80);
		return self::createJavaUuid(bin2hex($data));
	}

	public function createJavaUuid($striped) {
		//example: 069a79f4-44e9-4726-a5be-fca90e38aaf5
		$components = array(
		substr($striped, 0, 8),
		substr($striped, 8, 4),
		substr($striped, 12, 4),
		substr($striped, 16, 4),
		substr($striped, 20),
		);
		return implode('-', $components);
	}
}
