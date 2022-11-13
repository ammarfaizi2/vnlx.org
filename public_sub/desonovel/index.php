<?php

const BASE_HOSTNAME = "desonovel.blogspot.com";
const BASE_URL = "https://".BASE_HOSTNAME;
const XBASE_HOSTNAME = "desonovel.vnlx.org";
const XBASE_URL = "https://desonovel.vnlx.org";

function fixup_hostname(string $str): string
{
	$out = str_replace("https://".BASE_HOSTNAME, "https://".XBASE_HOSTNAME, $str);
	$out = str_replace("http://".BASE_HOSTNAME, "http://".XBASE_HOSTNAME, $out);
	$out = str_replace("//".BASE_HOSTNAME, "//".XBASE_HOSTNAME, $out);
	$out = str_replace(BASE_HOSTNAME, XBASE_HOSTNAME, $out);
	return $out;
}

function handle_response_header($ch, string $header)
{
	header(fixup_hostname($header));
	return strlen($header);
}

function handle_response_body($ch, $out)
{
	if (!is_string($out)) {
		http_response_code(503);
		header("Content-Type: text/plain");
		printf("Curl error: (%d) %s\n", curl_errno($ch), curl_error($ch));
		return;
	}

	echo fixup_hostname($out);
}

function do_request(string $url, string $method, array $headers)
{
	$ch = curl_init($url);
	curl_setopt_array($ch,
		[
			CURLOPT_RETURNTRANSFER	=> true,
			CURLOPT_SSL_VERIFYPEER	=> false,
			CURLOPT_SSL_VERIFYHOST	=> false,
			CURLOPT_CUSTOMREQUEST	=> $method,
			CURLOPT_HEADERFUNCTION	=> "handle_response_header",
			CURLOPT_VERBOSE		=> true
		]
	);
	$out = curl_exec($ch);
	handle_response_body($ch, $out);
	curl_close($ch);
}

function getallheaders_inline()
{
	$ret = [];
	$headers = getallheaders();
	foreach ($headers as $k => $v)
		$ret[] = "{$k}: {$v}";

	return $ret;
}

do_request(BASE_URL.$_SERVER["REQUEST_URI"], $_SERVER["REQUEST_METHOD"],
	   getallheaders_inline());
