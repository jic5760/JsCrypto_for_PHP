<?php
/*
 * JsCrypto_DefSubClass.php
 *
 * Created: 2016-06-25 08:30 GMT+9
 * Author: 이지찬 / Jichan Lee ( jic5760@naver.com / ablog.jc-lab.net )
 * License: MIT License
 */

if(!defined("__JSCRYPTO_DEFSUBCLASS_PHP__"))
{
define("__JSCRYPTO_DEFSUBCLASS_PHP__", 1);

class JsCrypto_DefSubClass {
	protected $m_uuid;
	protected $m_algorithm_name;
	protected $m_available_blocksizes;
	protected $m_available_keysizes;
	
	protected $m_setting_blocksize;
	
	public function getUUID() {
		return $this->m_uuid;
	}
	
	public function getAlgorithmName() {
		return $this->m_algorithm_name;
	}
	
	public function getAvailableBlockSizes() {
		return $this->m_available_blocksizes;
	}
	
	public function getAvailableKeySizes() {
		return $this->m_available_keysizes;
	}
	
	public function setBlockSize($blocksize) {
		if(in_array($blocksize, $this->m_available_blocksizes))
		{
			$this->m_setting_blocksize = $blocksize;
			return true;
		}else{
			$supportlist = "";
			for($i=0; $i<count($this->m_available_blocksizes); $i++)
			{
				if($i==0)
					$supportlist = $this->m_available_blocksizes[$i]."";
				else if($i==(count($this->m_available_blocksizes)-1))
					$supportlist .= "or ".$this->m_available_blocksizes[$i];
				else
					$supportlist = ", ".$this->m_available_blocksizes[$i];
			}
			
			trigger_error("Block Size of size ".$blocksize." not supported by this algorithm. Only keys of sizes ".$supportlist." supported", E_USER_ERROR);
			return false;
		}
	}
	
	public function getBlockSize() {
		return $this->m_setting_blocksize;
	}
	
	public function setKey($key) {
		return false;
	}
};

}
?>
