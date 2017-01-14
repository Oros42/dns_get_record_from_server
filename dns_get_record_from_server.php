<?php
/* 
 * Get IPv4 and IPv6 addresses of a domain from a specific DNS server.
 *
 * Author: Oros
 * 2017/01/14
 * License : Public domain
 */

/**
 * Input
 * $domain : a domain
 * 		Ex: $domain="debian.org"
 * $dns_server : IP of the DNS server.
 * 		Ex : $dns_server="80.67.169.12" (FDN)
 * 
 * Return :
 * 		- if input error :
 * 			-1
 * 		- if connexion to $dns_server failed :
 * 			-2
 * 		- if no addresses :
 * 			array(
 * 				4 => array(),
 *				6 => array()
 *			)
 * 		- if get IPs :
 * 			array(
 * 				4 => array(
 * 					0 => "130.89.148.14",
 * 					1 => "140.211.15.34",
 * 					2 => "128.31.0.62",
 * 					3 => "5.153.231.4",
 * 				),
 *				6 => array(
 * 					0 => "2001:610:1908:b000::148:14",
 * 					1 => "2001:41c8:1000:21::21:4"
 * 				)
 *			)	
 */
function dns_get_record_from_server($domain, $dns_server){
	if(empty($domain) || empty($dns_server) || filter_var($dns_server, FILTER_VALIDATE_IP) === false){
		return -1;
	}
	if(strpbrk($domain, "'\" %<>;*/\\=")){
		return -1;
	}
	$new_domain = parse_url("http://".$domain, PHP_URL_HOST);
	if(!strpos($new_domain, ".") || $new_domain != $domain){
		return -1;
	}

	if (!filter_var($dns_server, FILTER_VALIDATE_IP, FILTER_FLAG_IPV6) === false) {
		$dns_server = "[".$dns_server."]";
	}

	$domain .= ".";
	$timeout = 2;
	$QCLASS = 1; // IN
	$ips = array(4=>[], 6=>[]);
	foreach ([1, 28] as $QTYPE) { /* 1==A , 28==AAAA, https://tools.ietf.org/html/rfc1035 */
		$data = pack('n6', rand(10, 77), 0x0100, 1, 0, 0, 0);

		foreach (explode('.', $domain) as $bit) {
		    $data .= chr(strlen($bit)) . $bit;
		}
		$data .= pack('n2', $QTYPE, $QCLASS);

		foreach (explode('.', $domain) as $bit) {
		    $data .= chr(strlen($bit)) . $bit;
		}
		$data .= "\0\0\0\2\0\1";

		$errno = $errstr = 0;
		$fp = @fsockopen('udp://' . $dns_server, 53, $errno, $errstr, $timeout);
		if (!$fp || !is_resource($fp)){
			return -2;
		}
		socket_set_timeout($fp, $timeout);
		fwrite($fp, $data);
		$response_data = fread( $fp, 8192 );
		fclose($fp);
		if(empty($response_data)){
			return -1;
		}

		$answer_header = unpack( "nTransactionID/nFlags/nQuestionsCount/nAnswersCount/nAuthorityCount/nAdditionalCount", substr($response_data, 0, 12));

		if($answer_header['AnswersCount'] > 0){
			// Queries offset
			$offset = 12 + strlen( $domain ) + 7;

			for ($i=0; $i < $answer_header['AnswersCount']; $i++) {
				$record_header = unpack("nType/nClass/Nttl/nDataLength", substr($response_data, $offset, 10));
				$offset += 10;
				if($record_header['DataLength'] == 4){
					$ips[4][] = implode(".",unpack("C*", substr($response_data, $offset, 4)));
					$offset += 4;
				}elseif($record_header['DataLength'] == 16){
					$ips[6][] = inet_ntop(inet_pton(substr(chunk_split(unpack("H*",substr($response_data,$offset,16))[1], 4, ':'),0,-1)));
					$offset += 16;
				}
				$offset += 2;
			}
		}
	}
	return $ips;
}
?>