<?php

class DofusWeb_API
{
	const 	ACCESS_DONE 	= 0;
	const 	ACCESS_FAIL 	= 1;
	const 	ACCESS_CGU 		= 2;
	const 	ACCESS_SHIELD 	= 3;
	public 	$body;
	public 	$code;
	public 	$dataAccount;
	public 	$dataDofus;	
	public 	$errors;
	private $_cookie;
	private $_login;

	public function __construct($username, $password)
	{
		if (!is_string($username) || !is_string($password))
			throw new Exception('ERROR :(');
		$this->setLogin($username, $password);
	}
	public function __destruct()
	{
		if (@file_exists($rhis->cookie))
		{
			@unlink($this->_cookie);
		}
	}

	public function askIsConnected($reload=false)
	{
		if ($reload)
		{
			// Reload Ankama or Dofus Homepage !
		}
		if (preg_match('#Gestion de compte.*?</a>#', $this->body))
			return true;
		return false;
	}

	public function collectAnkamaData()
	{
		$this->errors = array();
		if (!$this->askIsConnected())
		{
			$this->errors[] = "Not connected !";
			return false;
		}
		$r = NULL;
		if (($r = $this->_askAnkamaAccess()) == self::ACCESS_FAIL)
		{
			$this->errors[] = "Not connected !";
			return false;			
		}
		$this->dataAccount['security'] = $r;
		if ($r != self::ACCESS_DONE)
			return false;
		$this->dataAccount['account'] = $this->_parseAnkamaAccount();
		return true;
	}

	public function collectDofusData()
	{
		$this->errors = array();
		if (!$this->askIsConnected())
		{
			$this->errors[] = "Not connected !";
			return false;
		}
		$this->dataDofus['account'] 	= $this->_parseDofusAccount();
		$this->dataDofus['characters'] 	= $this->_parseDofusCharacters();
		//$this->dataDofus['notifications'] = $this->_parseDofusNotifications();
		return true;
	}

	public function setCookie($path_to_file)
	{
		$this->errors = array();
		$this->_cookie = $path_to_file;
		if (@file_put_contents($this->_cookie, "") === false)
		{
			$this->errors[] = "Can't create a cookie file.";
			$this->_cookie = NULL;
			return false;
		}
		return true;
	}

	public function setLogin($username, $password)
	{
		$this->errors = array();
		if (!is_string($username) || !is_string($password))
		{
			$this->errors[] = "Parameters are not of type string.";
			return false;
		}
		$this->_login = array(
			'user' => $username,
			'pass' => $password 
		);
		return true;
	}
	
	public function getCookie()
	{
		return $this->_cookie;
	}

	public function getLogin()
	{
		return $this->_login;
	}

	public function reqAnkamaHome()
	{
		$ret = $this->_doRequest(array(
			'url' 		=> 'https://account.ankama.com/fr/votre-compte/profil',
			'type' 		=> 'GET',
			'cookie' 	=> $this->_cookie
		));
		if (!$ret || $this->code != 200)
			return false;
		return true;
	}

	public function reqAnkamaLogin()
	{
		$ret = $this->_doRequest(array(
			'url' 		=> 'https://account.ankama.com/sso',
			'type' 		=> 'POST',
			'fields' 	=> array(
				'action' 	=> "login",
				'from' 	 	=> "https://account.ankama.com/fr/votre-compte/profil",
				'postback'	=> 1,
				'login'  	=> $this->_login['user'],
				'password' 	=> $this->_login['pass']
			),
			'cookie' 	=> $this->_cookie
		));
		if (!$ret || $this->code != 200)
			return false;
		return $this->askIsConnected();
	}

	public function reqAnkamaLogout()
	{
		if (!$this->askIsConnected())
			return true;
		$ret = $this->_doRequest(array(
			'url' 		=> 'https://account.ankama.com/sso?action=logout&from=https://account.ankama.com/fr/votre-compte/profil',
			'type' 		=> 'GET',
			'cookie' 	=> $this->_cookie
		));
		if (!$ret || $this->code != 200)
			return false;
		return ($this->askIsConnected() ? false : true);
	}

	public function reqDofusHome()
	{
		$ret = $this->_doRequest(array(
			'url' 		=> 'http://www.dofus.com/fr',
			'type' 		=> 'GET',
			'cookie' 	=> $this->_cookie
		));
		if (!$ret || $this->code != 200)
			return false;
		return true;
	}

	public function reqDofusLogin()
	{
		$ret = $this->_doRequest(array(
			'url' 		=> 'https://account.ankama.com/sso',
			'type' 		=> 'POST',
			'fields' 	=> array(
				'action' 	=> "login",
				'from' 	 	=> "http://www.dofus.com/fr",
				'login'  	=> $this->_login['user'],
				'password' 	=> $this->_login['pass']
			),
			'cookie' 	=> $this->_cookie
		));
		if (!$ret || $this->code != 200)
			return false;
		if ($this->askIsConnected())
			return true;
		// get error message
		$this->errors = array();
		$matches = array();
		if (preg_match('#<span class="warning errors_login_([a-z]+?)" style>(.*?)</span>#', $this->body, $matches))
		{
			$this->errors[] = $matches[1];
			$this->errors[] = $matches[2];
		}
		else
			$this->errors[] = "unknown";
		return false;
	}

	public function reqDofusLogout()
	{
		if (!$this->askIsConnected())
			return true;
		$ret = $this->_doRequest(array(
			'url' 		=> 'https://account.ankama.com/sso?action=logout&from=http://www.dofus.com/fr',
			'type' 		=> 'GET',
			'cookie' 	=> $this->_cookie
		));
		if (!$ret || $this->code != 200)
			return false;
		return ($this->askIsConnected() ? false : true);
	}

	private function _askAnkamaAccess($reload=false)
	{
		$this->errors = array();
		if ($reload)
		{
			if (!$this->reqAnkamaHome() || !$this->askIsConnected())
			{
				$this->errors[] = "Reload failed or not connected.";
				return self::ACCESS_FAIL;
			}
		}
		else if (!$this->askIsConnected())
		{
			$this->errors[] = "You need to be connected.";
			return self::ACCESS_FAIL;
		}
		if (preg_match('#PRESENTATION DES CGU#', $this->body))
		{
			$this->errors[] = "!Access -> CGU";
			return self::ACCESS_CGU;
		}
		if (preg_match('#Le mode restreint est activ#', $this->body))
		{
			$this->errors[] = "!Access -> SHIELD";
			return self::ACCESS_SHIELD;
		}
		return self::ACCESS_DONE;
	}

	private function _doRequest($options)
	{
		$this->code = NULL;
		$this->body = NULL;
		$this->errors = array();

		if (empty($options['url']))
			return false;
		if (!isset($options['useragent']))
			$options['useragent'] = "Mozilla 5.0";
		$hc = curl_init($options['url']);
		curl_setopt($hc, CURLOPT_FRESH_CONNECT, true);
		curl_setopt($hc, CURLOPT_SSL_VERIFYPEER, false); 
		curl_setopt($hc, CURLOPT_SSL_VERIFYHOST, 0); 
		curl_setopt($hc, CURLOPT_FOLLOWLOCATION, true); 
		curl_setopt($hc, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($hc, CURLOPT_COOKIEJAR, realpath($options['cookie']));
		curl_setopt($hc, CURLOPT_COOKIEFILE, realpath($options['cookie']));
		curl_setopt($hc, CURLOPT_USERAGENT, $options['useragent']);
		if (isset($options['type']) && $options['type'] == 'POST')
		{
			curl_setopt($hc, CURLOPT_POST, true);
			curl_setopt($hc, CURLOPT_POSTFIELDS, $options['fields']);
		}
		if (($this->body = utf8_decode(curl_exec($hc))) === false)
			$this->errors[] = curl_error($hc);
		$this->code = curl_getinfo($hc, CURLINFO_HTTP_CODE); 
		curl_close($hc);
		return true;
	}

	private function _parseAnkamaAccount()
	{
		$r_array = array();
		$matches = array();

		preg_match_all('#<div class="body">(.*?)</div>#s', $this->body, $matches);
		$sub_matches = array();
		if (preg_match('#<b>(.*?)</b>#', $matches[1][0], $sub_matches))
			$r_array['nickname'] = $sub_matches[1];
		else
			$r_array['nickname'] = NULL;
		if (preg_match('#<b>(.*?)&nbsp;(.*?)</b>.*?le(.*?)<br />#s', $matches[1][1], $sub_matches))
		{
			$r_array['firstname'] = $sub_matches[1];
			$r_array['lastname'] = $sub_matches[2];
			$r_array['birth'] = trim($sub_matches[3]);
		}
		else
		{
			$r_array['firstname'] = NULL;
			$r_array['lastname'] = NULL;
			$r_array['birth'] = NULL;
		}
		if (preg_match('#<b>(.*?)</b>#', $matches[1][2], $sub_matches))
			$r_array['email'] = $sub_matches[1];
		else
			$r_array['email'] = NULL;
		if (preg_match('#portable.*?<b>(.*?)</b>.*?fixe.*?<b>(.*?)</b>#s', $matches[1][3], $sub_matches))
		{
			$r_array['portable'] = $sub_matches[1];
			$r_array['fixe'] = $sub_matches[2];
		}
		else
		{
			$r_array['portable'] = NULL;
			$r_array['fixe'] = NULL;
		}
		$r_array['password'] = trim($matches[1][4]);
		$r_array['address'] = explode('<br />', trim($matches[1][5]));
		$r_array['address'][count($r_array['address'])-1] = explode("<p", $r_array['address'][count($r_array['address'])-1]);
		$r_array['address'][count($r_array['address'])-1] = $r_array['address'][count($r_array['address'])-1][0];
		foreach ($r_array['address'] as &$v){
			$v = trim($v);
		}
		unset($v);
		$r_array['address'] = implode('|', $r_array['address']);
		if (preg_match('#Certains champs.*?sont manquants#i', $r_array['address']))
			$r_array['address'] = NULL;
		if (!preg_match('#<span>Compte certifi.{1}</span>#s', $matches[1][7]))
			$r_array['certified'] = false;
		else
			$r_array['certified'] = true;
		return $r_array;
	}

	private function _parseDofusAccount()
	{
		$r_array = array();
		$matches = array();
		// Read Nickname
		if (preg_match('#class="ak-infos-nickname">(.*?)</span>#', $this->body, $matches))
			$r_array['nickname'] = $matches[1];
		else
			$r_array['nickname'] = NULL;
		// Read Subscription
		if (preg_match('#subscribe">(.*?)<span class="ak-infos-title">DOFUS</span>#s', $this->body, $matches))
			$r_array['subscription'] = trim(html_entity_decode($matches[1]));
		else
			$r_array['subscription'] = NULL;
		if (preg_match('#<span class="ak-infos-small">jusqu\'au (.*?)</span>#', $this->body, $matches))
			$r_array['subs_expiration'] = trim($matches[1]);
		else
			$r_array['subs_expiration'] = NULL;
		// Read Ogrines, Kroz and Ankabox
		if (preg_match_all ('#<div class="ak-row-cell ak-infos-logged">(.*?)</div>#s', $this->body, $matches))
		{
			foreach ($matches[1] as $string){
				$rmatches = array();
				if (preg_match('#class="ak-infos-title">([a-zA-Z]*).*?: </span>.*?class="ak-infos-nb">(.*?)</span>#s', $string, $rmatches))
					$r_array[strtolower(trim( htmlentities(preg_replace("/\s/",'',html_entity_decode($rmatches[1]))) ))] = $rmatches[2];
			}
		}
		return $r_array;
	}

	private function _parseDofusCharacters()
	{
		$r_array = array();
		$matches = array();
		// Read Class and Level
		if (!preg_match_all ('#<div class="ak-level">(.*?)</div>#s', $this->body, $matches))
			return $r_array;
		foreach ($matches[1] as $v) {
			$index = count($r_array);
			$parse = explode(' -', $v);
			$r_array[$index]['class'] = $parse[0];
			$parse = explode(' : ', $v);
			$r_array[$index]['level'] = intval($parse[1]);
		}
		// Read Name
		if (!preg_match_all('#<div class="ak-name">.*?<a href="/fr/mmorpg/communaute/annuaires/pages-persos/[0-9]+-.*?">(.*?)</a>#s', $this->body, $matches))
			return $r_array;
		$index = 0;
		foreach ($matches[1] as $v) {
			$r_array[$index]['name'] = $v;
			$index += 1;
		}
		// Read Server
		if (!preg_match_all('#<div class="ak-server">(.*?)</div>#s', $this->body, $matches))
			return $r_array;
		$index = 0;
		foreach ($matches[1] as $v) {
			$r_array[$index]['server'] = $v;
			$index += 1;
		}
		$temp = array();
		foreach ($r_array as $key => $row)
			$temp[$key] = $row['level'];
		array_multisort($temp, SORT_DESC, $r_array);
		return $r_array;
	}

	/*
	private function _parseDofusNotifications()
	{
		$this->notifications = array();

		$matches = array();
		if (!preg_match_all ('#<div class="ak-notification">(.*?)</div>#s', $this->body, $matches))
			return false;
		foreach ($matches[1] as $v) {
			$index = count($this->notifications);
			$this->notifications[$index]['note'] = $v;
		}
		return true;
	}
	*/	
}
