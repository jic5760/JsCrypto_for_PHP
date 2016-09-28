<?php
require_once("./JsCrypto.php");

//JsCrypto_loadAlgorithm("LEA"); // 특정 알고리즘만 로드
//JsCrypto_loadAlgorithm("HIGHT"); // 특정 알고리즘만 로드
JsCrypto_loadAllAlgorithm(); // 모든 알고리즘 로드

$key = "\x0f\x1e\x2d\x3c\x4b\x5a\x69\x78\x87\x96\xa5\xb4\xc3\xd2\xe1\xf0\xf0\xe1\xd2\xc3\xb4\xa5\x96\x87\x78\x69\x5a\x4b\x3c\x2d\x1e\x0f";
$plaintext = "\x30\x31\x32\x33\x34\x35\x36\x37\x38\x39\x3a\x3b\x3c\x3d\x3e\x3f";
$ciphertext = JsCrypto_Encrypt(JSCRYPTO_LEA_128, $key, $plaintext, JSCRYPTO_MODE_CBC, "0000000000000000");
$decrypttext = JsCrypto_Decrypt(JSCRYPTO_LEA_128, $key, $ciphertext, JSCRYPTO_MODE_CBC, "0000000000000000");

echo "plaintext&nbsp;&nbsp;&nbsp;: ".bin2hex($plaintext)."<br />\n";
echo "ciphertext&nbsp;&nbsp;: ".bin2hex($ciphertext)."<br />\n";
echo "decrypttext&nbsp;: ".bin2hex($decrypttext)."<br />\n";

echo "<br /><br />\n\n";

$key = "\x01\x02\x03\x04\x05\x06\x07\x08\x09\x0a\x0b\x0c\x0d\x0e\x0f\x10";
$plaintext = "\x00\x01\x02\x03\x04\x05\x06\x07";
$ciphertext = JsCrypto_Encrypt(JSCRYPTO_HIGHT_64, $key, $plaintext, JSCRYPTO_MODE_CBC, "00000000");
$decrypttext = JsCrypto_Decrypt(JSCRYPTO_HIGHT_64, $key, $ciphertext, JSCRYPTO_MODE_CBC, "00000000");

echo "plaintext&nbsp;&nbsp;&nbsp;: ".bin2hex($plaintext)."<br />\n";
echo "ciphertext&nbsp;&nbsp;: ".bin2hex($ciphertext)."<br />\n";
echo "decrypttext&nbsp;: ".bin2hex($decrypttext)."<br />\n";

/*
Supported ciphers

JSCRYPTO_HIGHT_64 : HIGHT Algorithm, Block size is 8 bytes
	Key size : 16 Bytes
JSCRYPTO_LEA_128 : LEA Algorithm, Block size is 16 bytes
	Key size : 16 / 24 / 32 Bytes

==================================================
위 코드 실행 시

﻿﻿﻿plaintext   : 303132333435363738393a3b3c3d3e3f
ciphertext  : 8c828359deeda5d33fc3b07855d6ddbf
decrypttext : 303132333435363738393a3b3c3d3e3f


plaintext   : 0001020304050607
ciphertext  : 70a32bde920f4548
decrypttext : 0001020304050607

이렇게 나오면 정상입니다.
*/
?>
