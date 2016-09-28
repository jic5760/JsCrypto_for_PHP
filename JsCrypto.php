<?php
/*
 * JsCrypto.php
 *
 * Created: 2016-06-25 09:09 GMT+9
 * Author: 이지찬 / Jichan Lee ( jic5760@naver.com / ablog.jc-lab.net )
 * License: MIT License
 */

if(!defined("__JSCRYPTO_PHP__"))
{
define("__JSCRYPTO_PHP__", 1);

define("JSCRYPTO_MODE_ECB", 1);
define("JSCRYPTO_MODE_CBC", 2);

function JsCrypto_loadAlgorithm($name)
{
	require_once(dirname(__FILE__)."/JsCrypto_".$name.".php");
}

function JsCrypto_loadAllAlgorithm()
{
	if ($handle = opendir(dirname(__FILE__)))
	{
		while (false !== ($entry = readdir($handle))) {
			if($entry == "." || $entry == "..") continue;
			if(strcmp(substr($entry, 0, 9), "JsCrypto_") != 0) continue;
			$p = strrpos($entry, ".");
			if($p === false) continue;
			$ext = substr($entry, $p + 1);
			if(strcmp($ext, "php") != 0) continue;
			$name = substr($entry, 9, $p - 9);
			if(strcmp($name, "DefSubClass") == 0) continue;
			JsCrypto_loadAlgorithm($name);
		}
		closedir($handle);
	}
}

function JsCrypto_NewCipherClass($cipher)
{
	// ClassName:UUID:BlockSize
	$cipher_arr = explode(":", $cipher, 3);
	
	if(count($cipher_arr) != 3)
	{
		trigger_error("Module initialization failed (Wrong cipher1)", E_USER_ERROR);
		return false;
	}
	
	if(strpos($cipher_arr[0], "JsCrypto_") != 0)
	{
		trigger_error("Module initialization failed (Wrong cipher2)", E_USER_ERROR);
		return false;
	}
	
	if(!class_exists($cipher_arr[0]))
	{
		trigger_error("Module initialization failed (Not loaded algorithm)", E_USER_ERROR);
		return false;
	}
	
	if(!is_numeric($cipher_arr[2]))
	{
		trigger_error("Module initialization failed (Wrong Block Size)", E_USER_ERROR);
		return false;
	}
	
	$cls = new $cipher_arr[0]();
	if(strcasecmp($cls->getUUID(), $cipher_arr[1])!=0)
	{
		trigger_error("Module initialization failed (Algorithm is not matched)", E_USER_ERROR);
		return false;
	}
	
	$blocksize = @intval($cipher_arr[2]);
	
	if(!$cls->setBlockSize($blocksize))
	{
		return false;
	}
	
	return $cls;
}

function JsCrypto_Encrypt($cipher, $key, $data, $mode, $iv = NULL)
{
	$cls = JsCrypto_NewCipherClass($cipher);
	if($cls === false)
	{
		return false;
	}
	
	$blocksize = $cls->getBlockSize();
	
	if(!$cls->setKey($key))
	{
		return false;
	}
	
	$bufsize = $data_in_size = strlen($data);
	if($bufsize % $blocksize)
	{
		$bufsize += $blocksize - ($bufsize % $blocksize);
	}
	$numofblocks = $bufsize / $blocksize;
	
	$arrdata_in = array_fill(0, $bufsize, 0);
	$arrdata_out = array();
	
	for($i=0;$i<$data_in_size;$i++)
		$arrdata_in[$i] = ord(substr($data, $i, 1));
	
	switch($mode) {
		case JSCRYPTO_MODE_ECB:
		{
			for($i=0; $i<$numofblocks; $i++)
			{
				$tmpdata_in = array_slice($arrdata_in, $i*$blocksize, $blocksize);
				$tmpdata_out = $cls->BlockEncryptArr($tmpdata_in);
				$arrdata_out = array_merge($arrdata_out, $tmpdata_out);
			}
		}
		break;
		case JSCRYPTO_MODE_CBC:
		{
			$iv_arr = array_fill(0, $blocksize, 0);
			if($iv != NULL)
			{
				if(strlen($iv) != $blocksize)
				{
					trigger_error("IV size of ".strlen($iv)." wrong", E_USER_ERROR);
					return false;
				}
				for($j=0; $j<$blocksize; $j++)
					$iv_arr[$i] = ord(substr($iv, $i, 1));
			}
			for($i=0; $i<$numofblocks; $i++)
			{
				$tmpdata_in = array_slice($arrdata_in, $i*$blocksize, $blocksize);
				for($j=0;$j<$blocksize;$j++)
					$tmpdata_in[$j] ^= $iv_arr[$j];
				$tmpdata_out = $cls->BlockEncryptArr($tmpdata_in);
				$iv_arr = $tmpdata_out;
				$arrdata_out = array_merge($arrdata_out, $tmpdata_out);
			}
		}
		break;
		default:
			trigger_error("Mode of ".$mode." not supported", E_USER_ERROR);
			return false;
	}
	
	$data_out = "";
	for($i=0;$i<$bufsize;$i++)
	{
		$data_out .= chr($arrdata_out[$i]);
	}
	
	return $data_out;
}

function JsCrypto_Decrypt($cipher, $key, $data, $mode, $iv = NULL)
{
	$cls = JsCrypto_NewCipherClass($cipher);
	if($cls === false)
	{
		return false;
	}
	
	$blocksize = $cls->getBlockSize();
	
	if(!$cls->setKey($key))
	{
		return false;
	}
	
	$bufsize = strlen($data);
	if($bufsize % $blocksize)
	{
		trigger_error("Data size of ".$bufsize." not supported", E_USER_ERROR);
		return false;
	}
	$numofblocks = $bufsize / $blocksize;
	
	$arrdata_in = array_fill(0, $bufsize, 0);
	$arrdata_out = array();
	
	for($i=0;$i<$bufsize;$i++)
		$arrdata_in[$i] = ord(substr($data, $i, 1));
	
	switch($mode) {
		case JSCRYPTO_MODE_ECB:
		{
			for($i=0; $i<$numofblocks; $i++)
			{
				$tmpdata_in = array_slice($arrdata_in, $i*$blocksize, $blocksize);
				$tmpdata_out = $cls->BlockDecryptArr($tmpdata_in);
				$arrdata_out = array_merge($arrdata_out, $tmpdata_out);
			}
		}
		break;
		case JSCRYPTO_MODE_CBC:
		{
			$iv_arr = array_fill(0, $blocksize, 0);
			if($iv != NULL)
			{
				if(strlen($iv) != $blocksize)
				{
					trigger_error("IV size of ".strlen($iv)." wrong", E_USER_ERROR);
					return false;
				}
				for($j=0; $j<$blocksize; $j++)
					$iv_arr[$i] = ord(substr($iv, $i, 1));
			}
			for($i=0; $i<$numofblocks; $i++)
			{
				$tmpdata_in = array_slice($arrdata_in, $i*$blocksize, $blocksize);
				$tmpdata_out = $cls->BlockDecryptArr($tmpdata_in);
				for($j=0;$j<$blocksize;$j++)
					$tmpdata_out[$j] ^= $iv_arr[$j];
				$iv_arr = $tmpdata_in;
				$arrdata_out = array_merge($arrdata_out, $tmpdata_out);
			}
		}
		break;
		default:
			trigger_error("Mode of ".$mode." not supported", E_USER_ERROR);
			return false;
	}
	
	$data_out = "";
	for($i=0;$i<$bufsize;$i++)
	{
		$data_out .= chr($arrdata_out[$i]);
	}
	
	return $data_out;
}

} /* __JSCRYPTO_PHP__ */
?>
