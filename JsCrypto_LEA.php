<?php
/*
 * JsCrypto_LEA.php
 *
 * Created: 2016-06-20 23:20:36 GMT+9
 * Author: 이지찬 / Jichan Lee ( jic5760@naver.com / ablog.jc-lab.net )
 * License: MIT License
 */

if(!defined("__JSCRYPTO_LEA_PHP__"))
{
define("__JSCRYPTO_LEA_PHP__", 1);

define("JSCRYPTO_LEA_128", "JsCrypto_LEA:9775fab9-122c-4ecb-9bfc-3d8dedce51ed:16");

require_once(dirname(__FILE__)."/JsCrypto_DefSubClass.php");

class JsCrypto_LEA extends JsCrypto_DefSubClass {
	private $LEA_delta = array(
		0xc3efe9db,
		0x44626b02,
		0x79e27c8a,
		0x78df30ec,
		0x715ea49e,
		0xc785da0a,
		0xe04ef22a,
		0xe5c40957
	);
	
	private $m_my_Nr;
	private $m_my_RoundKey_encryption;
	private $m_my_RoundKey_decryption;
	
	public function __construct()
	{
		$this->m_algorithm_name = "LEA";
		$this->m_uuid = "9775fab9-122c-4ecb-9bfc-3d8dedce51ed";
		$this->m_available_blocksizes = array(16);
		$this->m_available_keysizes = array(16, 24, 32);
	}
	
	public function setKey($key)
	{
		$keysize = strlen($key);
		if(!in_array($keysize, $this->m_available_keysizes))
		{
			trigger_error("Key of size ".$keysize." not supported by this algorithm. Only keys of sizes 16, 24 or 32 supported", E_USER_ERROR);
			return false;
		}
		
		$this->m_my_Nr = self::KeysizeToNr($keysize);
		
		$arrKey = array_fill(0, $keysize, 0);
		for($i=0;$i<$keysize;$i++)
			$arrKey[$i] = ord(substr($key, $i, 1));
		
		switch($keysize)
		{
			case 16:	
				$this->m_my_RoundKey_encryption = self::EncryptKeySchedule_128($arrKey);
				break;
			case 24:	
				$this->m_my_RoundKey_encryption = self::EncryptKeySchedule_192($arrKey);
				break;
			case 32:	
				$this->m_my_RoundKey_encryption = self::EncryptKeySchedule_256($arrKey);
				break;
		}
		
		$this->m_my_RoundKey_decryption = array(0, count($this->m_my_RoundKey_encryption), 0);
		for($i=0;$i<$this->m_my_Nr;$i++)
		{
			for($j=0; $j<6; $j++)
				$this->m_my_RoundKey_decryption[$i * 6 + $j] = $this->m_my_RoundKey_encryption[($this->m_my_Nr - $i - 1) * 6 + $j];
		}
		
		return true;
	}
	
	private function LEA_ROL($places, $value)
	{
		if($places >= 32) $places -= 32;
		$a = $value<<$places;
		$a &= 0xFFFFFFFF;
		$amask = ~((1 << $places) - 1);
		$a = $a & $amask;
		$b = $value >> (32-$places);
		$b &= 0xFFFFFFFF;
		$bmask = ~$amask;
		$b = $b & $bmask;
		return ($a | $b);
	}
	
	private function LEA_ROR($places, $value)
	{
		if($places >= 32) $places -= 32;
		$a = $value>>$places;
		$a &= 0xFFFFFFFF;
		$amask = ~(((1<<$places) - 1) << (32-$places));
		$a = $a & $amask;
		$b = $value << (32-$places);
		$b &= 0xFFFFFFFF;
		$bmask = ~$amask;
		$b = $b & $bmask;
		
		return ($a | $b);
	}
	
	private function LEA_w_plus($a, $b)
	{
		return (((int)$a + (int)$b) & 0xFFFFFFFF);
	}
	
	private function LEA_w_minus($a, $b)
	{
		return (((int)$a - (int)$b) & 0xFFFFFFFF);
	}
	
	private function EncryptKeySchedule_128($K)
	{
		$RoundKey = array_fill(0, 144, 0);
		$T = array_fill(0, 4, 0);
		for($i=0; $i<4; $i++)
		{
			$T[$i] = ($K[$i*4+0] << 0) | ($K[$i*4+1] << 8) | ($K[$i*4+2] << 16) | ($K[$i*4+3] << 24);
		}
		for($i=0; $i<24; $i++)
		{
			$T[0] = $this->LEA_ROL(1, $this->LEA_w_plus($T[0], $this->LEA_ROL($i, $this->LEA_delta[$i % 4])));
			$T[1] = $this->LEA_ROL(3, $this->LEA_w_plus($T[1], $this->LEA_ROL($i+1, $this->LEA_delta[$i % 4])));
			$T[2] = $this->LEA_ROL(6, $this->LEA_w_plus($T[2], $this->LEA_ROL($i+2, $this->LEA_delta[$i % 4])));
			$T[3] = $this->LEA_ROL(11, $this->LEA_w_plus($T[3], $this->LEA_ROL($i+3, $this->LEA_delta[$i % 4])));
			
			$RoundKey[$i * 6 + 0] = $T[0];
			$RoundKey[$i * 6 + 1] = $T[1];
			$RoundKey[$i * 6 + 2] = $T[2];
			$RoundKey[$i * 6 + 3] = $T[1];
			$RoundKey[$i * 6 + 4] = $T[3];
			$RoundKey[$i * 6 + 5] = $T[1];
		}
		
		return $RoundKey;
	}
	
	private function EncryptKeySchedule_192($K)
	{
		$RoundKey = array_fill(0, 168, 0);
		$T = array_fill(0, 6, 0);
		for($i=0; $i<6; $i++)
		{
			$T[$i] = ($K[$i*4+0] << 0) | ($K[$i*4+1] << 8) | ($K[$i*4+2] << 16) | ($K[$i*4+3] << 24);
		}
		for($i=0; $i<28; $i++)
		{
			$T[0] = $this->LEA_ROL(1, $this->LEA_w_plus($T[0], $this->LEA_ROL($i, $this->LEA_delta[$i % 6])));
			$T[1] = $this->LEA_ROL(3, $this->LEA_w_plus($T[1], $this->LEA_ROL($i+1, $this->LEA_delta[$i % 6])));
			$T[2] = $this->LEA_ROL(6, $this->LEA_w_plus($T[2], $this->LEA_ROL($i+2, $this->LEA_delta[$i % 6])));
			$T[3] = $this->LEA_ROL(11, $this->LEA_w_plus($T[3], $this->LEA_ROL($i+3, $this->LEA_delta[$i % 6])));
			$T[4] = $this->LEA_ROL(13, $this->LEA_w_plus($T[4], $this->LEA_ROL($i+4, $this->LEA_delta[$i % 6])));
			$T[5] = $this->LEA_ROL(17, $this->LEA_w_plus($T[5], $this->LEA_ROL($i+5, $this->LEA_delta[$i % 6])));
			
			$RoundKey[$i * 6 + 0] = $T[0];
			$RoundKey[$i * 6 + 1] = $T[1];
			$RoundKey[$i * 6 + 2] = $T[2];
			$RoundKey[$i * 6 + 3] = $T[3];
			$RoundKey[$i * 6 + 4] = $T[4];
			$RoundKey[$i * 6 + 5] = $T[5];
		}
		
		return $RoundKey;
	}
	
	private function EncryptKeySchedule_256($K)
	{
		$RoundKey = array_fill(0, 192, 0);
		$T = array_fill(0, 8, 0);
		for($i=0; $i<8; $i++)
		{
			$T[$i] = ($K[$i*4+0] << 0) | ($K[$i*4+1] << 8) | ($K[$i*4+2] << 16) | ($K[$i*4+3] << 24);
		}
		for($i=0; $i<32; $i++)
		{
			$T[(6 * $i + 0) % 8] = $this->LEA_ROL(1, $this->LEA_w_plus($T[0], $this->LEA_ROL($i, $this->LEA_delta[$i % 8])));
			$T[(6 * $i + 1) % 8] = $this->LEA_ROL(3, $this->LEA_w_plus($T[1], $this->LEA_ROL($i+1, $this->LEA_delta[$i % 8])));
			$T[(6 * $i + 2) % 8] = $this->LEA_ROL(6, $this->LEA_w_plus($T[2], $this->LEA_ROL($i+2, $this->LEA_delta[$i % 8])));
			$T[(6 * $i + 3) % 8] = $this->LEA_ROL(11, $this->LEA_w_plus($T[3], $this->LEA_ROL($i+3, $this->LEA_delta[$i % 8])));
			$T[(6 * $i + 4) % 8] = $this->LEA_ROL(13, $this->LEA_w_plus($T[4], $this->LEA_ROL($i+4, $this->LEA_delta[$i % 8])));
			$T[(6 * $i + 5) % 8] = $this->LEA_ROL(17, $this->LEA_w_plus($T[5], $this->LEA_ROL($i+5, $this->LEA_delta[$i % 8])));
			
			$RoundKey[$i * 6 + 0] = $T[(6 * $i + 0) % 8];
			$RoundKey[$i * 6 + 1] = $T[(6 * $i + 1) % 8];
			$RoundKey[$i * 6 + 2] = $T[(6 * $i + 2) % 8];
			$RoundKey[$i * 6 + 3] = $T[(6 * $i + 3) % 8];
			$RoundKey[$i * 6 + 4] = $T[(6 * $i + 4) % 8];
			$RoundKey[$i * 6 + 5] = $T[(6 * $i + 5) % 8];
		}
		
		return $RoundKey;
	}
	
	private function Round_Encrypt($Xin, $RKe, $Roffset)
	{
		$Xout = array_fill(0, 4, 0);
		$Xout[0] = $this->LEA_ROL(9, $this->LEA_w_plus($Xin[0]^$RKe[$Roffset+0],$Xin[1]^$RKe[$Roffset+1]));
		$Xout[1] = $this->LEA_ROR(5, $this->LEA_w_plus($Xin[1]^$RKe[$Roffset+2],$Xin[2]^$RKe[$Roffset+3]));
		$Xout[2] = $this->LEA_ROR(3, $this->LEA_w_plus($Xin[2]^$RKe[$Roffset+4],$Xin[3]^$RKe[$Roffset+5]));
		$Xout[3] = $Xin[0];
		return $Xout;
	}
	
	private function Round_Decrypt($Xin, $RKd, $Roffset)
	{
		$Xout = array_fill(0, 4, 0);
		$Xout[0] = $Xin[3];
		$Xout[1] = $this->LEA_w_minus($this->LEA_ROR(9, $Xin[0]), $Xout[0]^$RKd[$Roffset+0]) ^ $RKd[$Roffset+1];
		$Xout[2] = $this->LEA_w_minus($this->LEA_ROL(5, $Xin[1]), $Xout[1]^$RKd[$Roffset+2]) ^ $RKd[$Roffset+3];
		$Xout[3] = $this->LEA_w_minus($this->LEA_ROL(3, $Xin[2]), $Xout[2]^$RKd[$Roffset+4]) ^ $RKd[$Roffset+5];
		return $Xout;
	}
	
	public function KeysizeToNr($keybytes)
	{
		$Nr = false;
		switch($keybytes)
		{
			case 16:	
				$Nr = 24;
				break;
			case 24:	
				$Nr = 28;
				break;
			case 32:	
				$Nr = 32;
				break;
		}
		return $Nr;
	}
	
	private function _static_BlockEncrypt($Nr, $RoundKey, $arrPlaintext)
	{
		$X_cur = array_fill(0, 4, 0);
		$X_next = array_fill(0, 4, 0);
		
		for($i=0;$i<4;$i++)
			$X_cur[$i] = ((int)($arrPlaintext[$i*4+0]&0xFF)<<0) | ((int)($arrPlaintext[$i*4+1]&0xFF)<<8) | ((int)($arrPlaintext[$i*4+2]&0xFF)<<16) | ((int)($arrPlaintext[$i*4+3]&0xFF)<<24);
		
		for($i=0;$i<$Nr;$i++)
		{
			$X_next = $this->Round_Encrypt($X_cur, $RoundKey, $i * 6);
			$X_cur = $X_next;
		}
		
		$arrCiphertext = array_fill(0, 16, 0);
		for($i=0;$i<4;$i++)
		{
			$arrCiphertext[$i*4+0] = ($X_cur[$i] >> 0) & 0xFF;
			$arrCiphertext[$i*4+1] = ($X_cur[$i] >> 8) & 0xFF;
			$arrCiphertext[$i*4+2] = ($X_cur[$i] >> 16) & 0xFF;
			$arrCiphertext[$i*4+3] = ($X_cur[$i] >> 24) & 0xFF;
		}
		
		return $arrCiphertext;
	}
	
	private function _static_BlockDecrypt($Nr, $RoundKey, $arrPlaintext)
	{
		$X_cur = array_fill(0, 4, 0);
		$X_next = array_fill(0, 4, 0);
		
		for($i=0;$i<4;$i++)
			$X_cur[$i] = ((int)($arrPlaintext[$i*4+0]&0xFF)<<0) | ((int)($arrPlaintext[$i*4+1]&0xFF)<<8) | ((int)($arrPlaintext[$i*4+2]&0xFF)<<16) | ((int)($arrPlaintext[$i*4+3]&0xFF)<<24);
		
		for($i=0;$i<$Nr;$i++)
		{
			$X_next = $this->Round_Decrypt($X_cur, $RoundKey, $i * 6);
			$X_cur = $X_next;
		}
		
		$arrCiphertext = array_fill(0, 16, 0);
		for($i=0;$i<4;$i++)
		{
			$arrCiphertext[$i*4+0] = ($X_cur[$i] >> 0) & 0xFF;
			$arrCiphertext[$i*4+1] = ($X_cur[$i] >> 8) & 0xFF;
			$arrCiphertext[$i*4+2] = ($X_cur[$i] >> 16) & 0xFF;
			$arrCiphertext[$i*4+3] = ($X_cur[$i] >> 24) & 0xFF;
		}
		
		return $arrCiphertext;
	}
	
	public function BlockEncryptArr($arrdata)
	{
		return self::_static_BlockEncrypt($this->m_my_Nr, $this->m_my_RoundKey_encryption, $arrdata);
	}
	
	public function BlockDecryptArr($arrdata)
	{
		return self::_static_BlockDecrypt($this->m_my_Nr, $this->m_my_RoundKey_decryption, $arrdata);
	}
};

} /* __JSCRYPTO_LEA_PHP__ */
?>
