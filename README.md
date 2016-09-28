# JsCrypto

Ciphers:

	JSCRYPTO_HIGHT_64 : HIGHT Algorithm, Block size is 8 bytes
	
		Key size : 16 Bytes
		
	JSCRYPTO_LEA_128 : LEA Algorithm, Block size is 16 bytes
	
		Key size : 16 / 24 / 32 Bytes


Modes:

	JSCRYPTO_MODE_ECB : ECB (Electronic Codebook)
	
	JSCRYPTO_MODE_CBC : CBC (Cipher Block Chaining)
	

Functions:

	void JsCrypto_loadAlgorithm(string $algorithmName)
	
	void JsCrypto_loadAllAlgorithm()
	
	string JsCrypto_Encrypt($cipher, string $key, string $plaintext, $mode, string $iv)
	
	string JsCrypto_Decrypt($cipher, string $key, string $ciphertext, $mode, string $iv)
	
