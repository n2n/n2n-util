<?php
/*
 * Copyright (c) 2012-2016, Hofmänner New Media.
 * DO NOT ALTER OR REMOVE COPYRIGHT NOTICES OR THIS FILE HEADER.
 *
 * This file is part of the N2N FRAMEWORK.
 *
 * The N2N FRAMEWORK is free software: you can redistribute it and/or modify it under the terms of
 * the GNU Lesser General Public License as published by the Free Software Foundation, either
 * version 2.1 of the License, or (at your option) any later version.
 *
 * N2N is distributed in the hope that it will be useful, but WITHOUT ANY WARRANTY; without even
 * the implied warranty of MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU Lesser General Public License for more details: http://www.gnu.org/licenses/
 *
 * The following people participated in this project:
 *
 * Andreas von Burg.....: Architect, Lead Developer
 * Bert Hofmänner.......: Idea, Frontend UI, Community Leader, Marketing
 * Thomas Günther.......: Developer, Hangar
 */
namespace n2n\util\crypt;

class EncryptionDescriptor {
	
	const ALGORITHM_AES_128_CBC = 'AES-128-CBC';
	const ALGORITHM_AES_128_CCM = 'aes-128-ccm';
	const ALGORITHM_AES_128_CFB = 'AES-128-CFB';
	const ALGORITHM_AES_128_CFB1 = 'AES-128-CFB1';
	const ALGORITHM_AES_128_CFB8 = 'AES-128-CFB8';
	const ALGORITHM_AES_128_CTR = 'AES-128-CTR';
	const ALGORITHM_AES_128_ECB = 'AES-128-ECB';
	const ALGORITHM_AES_128_GCM = 'aes-128-gcm';
	const ALGORITHM_AES_128_OFB = 'AES-128-OFB';
	const ALGORITHM_AES_128_XTS = 'AES-128-XTS';
	
	const ALGORITHM_AES_192_CBC = 'AES-192-CBC';
	const ALGORITHM_AES_192_CCM = 'aes-192-ccm';
	const ALGORITHM_AES_192_CFB = 'AES-192-CFB';
	const ALGORITHM_AES_192_CFB1 = 'AES-192-CFB1';
	const ALGORITHM_AES_192_CFB8 = 'AES-192-CFB8';
	const ALGORITHM_AES_192_CTR = 'AES-192-CTR';
	const ALGORITHM_AES_192_ECB = 'AES-192-ECB';
	const ALGORITHM_AES_192_GCM = 'aes-192-gcm';
	const ALGORITHM_AES_192_OFB = 'AES-192-OFB';
	
	const ALGORITHM_AES_256_CBC = 'AES-256-CBC';
	const ALGORITHM_AES_256_CFB = 'AES-256-CFB';
	const ALGORITHM_AES_256_CCM = 'aes-256-ccm';
	const ALGORITHM_AES_256_CFB1 = 'AES-256-CFB1';
	const ALGORITHM_AES_256_CFB8 = 'AES-256-CFB8';
	const ALGORITHM_AES_256_CTR = 'AES-256-CTR';
	const ALGORITHM_AES_256_ECB = 'AES-256-ECB';
	const ALGORITHM_AES_256_GCM = 'aes-256-gcm';
	const ALGORITHM_AES_256_OFB = 'AES-256-OFB';
	const ALGORITHM_AES_256_XTS = 'AES-256-XTS';
	
	const ALGORITHM_BF_CBC = 'BF-CBC';
	const ALGORITHM_BF_CFB = 'BF-CFB';
	const ALGORITHM_BF_ECB = 'BF-ECB';
	const ALGORITHM_BF_OFB = 'BF-OFB';

	const ALGORITHM_CAMELLIA_128_CBC = 'CAMELLIA-128-CBC';
	const ALGORITHM_CAMELLIA_128_CFB = 'CAMELLIA-128-CFB';
	const ALGORITHM_CAMELLIA_128_CFB1 = 'CAMELLIA-128-CFB1';
	const ALGORITHM_CAMELLIA_128_CFB8 = 'CAMELLIA-128-CFB8';
	const ALGORITHM_CAMELLIA_128_ECB = 'CAMELLIA-128-ECB';
	const ALGORITHM_CAMELLIA_128_OFB = 'CAMELLIA-128-OFB';

	const ALGORITHM_CAMELLIA_192_CBC = 'CAMELLIA-192-CBC';
	const ALGORITHM_CAMELLIA_192_CFB = 'CAMELLIA-192-CFB';
	const ALGORITHM_CAMELLIA_192_CFB1 = 'CAMELLIA-192-CFB1';
	const ALGORITHM_CAMELLIA_192_CFB8 = 'CAMELLIA-192-CFB8';
	const ALGORITHM_CAMELLIA_192_ECB = 'CAMELLIA-192-ECB';
	const ALGORITHM_CAMELLIA_192_OFB = 'CAMELLIA-192-OFB';

	const ALGORITHM_CAMELLIA_256_CBC = 'CAMELLIA-256-CBC';
	const ALGORITHM_CAMELLIA_256_CFB = 'CAMELLIA-256-CFB';
	const ALGORITHM_CAMELLIA_256_CFB1 = 'CAMELLIA-256-CFB1';
	const ALGORITHM_CAMELLIA_256_CFB8 = 'CAMELLIA-256-CFB8';
	const ALGORITHM_CAMELLIA_256_ECB = 'CAMELLIA-256-ECB';
	const ALGORITHM_CAMELLIA_256_OFB = 'CAMELLIA-256-OFB';
	
	const ALGORITHM_CAST5_CBC = 'CAST5-CBC';
	const ALGORITHM_CAST5_CFB = 'CAST5-CFB';
	const ALGORITHM_CAST5_ECB = 'CAST5-ECB';
	const ALGORITHM_CAST5_OFB = 'CAST5-OFB';

	const ALGORITHM_DES_CBC = 'DES-CBC';
	const ALGORITHM_DES_CFB = 'DES-CFB';
	const ALGORITHM_DES_CFB1 = 'DES-CFB1';
	const ALGORITHM_DES_CFB8 = 'DES-CFB8';
	const ALGORITHM_DES_ECB = 'DES-ECB';
	const ALGORITHM_DES_OFB = 'DES-OFB';
	
	const ALGORITHM_DES_EDE = 'DES-EDE';
	const ALGORITHM_DES_EDE_CBC = 'DES-EDE-CBC';
	const ALGORITHM_DES_EDE_CFB = 'DES-EDE-CFB';
	const ALGORITHM_DES_EDE_OFB = 'DES-EDE-OFB';

	const ALGORITHM_DES_EDE3 = 'DES-EDE3';
	const ALGORITHM_DES_EDE3_CBC = 'DES-EDE3-CBC';
	const ALGORITHM_DES_EDE3_CFB = 'DES-EDE3-CFB';
	const ALGORITHM_DES_EDE3_CFB1 = 'DES-EDE3-CFB1';
	const ALGORITHM_DES_EDE3_CFB8 = 'DES-EDE3-CFB8';
	const ALGORITHM_DES_EDE3_OFB = 'DES-EDE3-OFB';
	
	const ALGORITHM_DESX_CBC = 'DESX-CBC';
	
	const ALGORITHM_ID_AES128_CCM = 'id-aes128-CCM';
	const ALGORITHM_ID_AES128_GCM = 'id-aes128-GCM';
	const ALGORITHM_ID_AES128_WRAP = 'id-aes128-wrap';
	
	const ALGORITHM_ID_AES192_CCM = 'id-aes192-CCM';
	const ALGORITHM_ID_AES192_GCM = 'id-aes192-GCM';
	const ALGORITHM_ID_AES192_WRAP = 'id-aes192-wrap';
	
	const ALGORITHM_ID_AES256_CCM = 'id-aes256-CCM';
	const ALGORITHM_ID_AES256_GCM = 'id-aes256-GCM';
	const ALGORITHM_ID_AES256_WRAP = 'id-aes256-wrap';
	
	const ALGORITHM_ID_SMIME_ALG_CMS3DESWRAP = 'id-smime-alg-CMS3DESwrap';
	
	const ALGORITHM_IDEA_CBC = 'IDEA-CBC';
	const ALGORITHM_IDEA_CFB = 'IDEA-CFB';
	const ALGORITHM_IDEA_ECB = 'IDEA-ECB';
	const ALGORITHM_IDEA_OFB = 'IDEA-OFB';
	
	const ALGORITHM_RC2_40_CBC = 'RC2-40-CBC';
	const ALGORITHM_RC2_64_CBC = 'RC2-64-CBC';
	const ALGORITHM_RC2_CBC = 'RC2-CBC';
	const ALGORITHM_RC2_CFB = 'RC2-CFB';
	const ALGORITHM_RC2_ECB = 'RC2-ECB';
	const ALGORITHM_RC2_OFB = 'RC2-OFB';

	const ALGORITHM_RC4 = 'RC4';
	const ALGORITHM_RC4_40 = 'RC4-40';
	const ALGORITHM_RC4_HMAC_MD5= 'RC4-HMAC-MD5';
	
	const ALGORITHM_SEED_CBC = 'SEED-CBC';
	const ALGORITHM_SEED_CFB = 'SEED-CFB';
	const ALGORITHM_SEED_ECB = 'SEED-ECB';
	const ALGORITHM_SEED_OFB = 'SEED-OFB';

	const DEFAULT_CRYPT_ALGORITHM = self::ALGORITHM_AES_256_CTR;
	/**
	* the openssl algorithm
	* initialised with the AES algorithm 
	* if you need a faster algorithm it is supposed to use ALGORITHM_AES_128_CBC
	* @var string
	*/
	private $algorithm;
	
	public function __construct($algorithm = self::DEFAULT_CRYPT_ALGORITHM) {
		$this->setAlgorithm($algorithm);
	}
	
	public function getAlgorithm() {
		return $this->algorithm;
	}
	
	public function setAlgorithm($algorithm) {
		if (!self::isAlgorithmAvailable($algorithm)) {
			throw new \InvalidArgumentException('n2n_error_crypt_algorithm_is_not_available: ' . $algorithm);
		}
		$this->algorithm = $algorithm;
	}
	
	public function generateKey() {
		if(!($length = $this->getKeySize())) return null;
		
		return OpenSslUtils::randomPseudoBytes($length);
	}
	
	public function generateIv() {
		if(!($length = $this->getIvSize())) return null;
		
		return OpenSslUtils::randomPseudoBytes($length);
	}
	
	public function getIvSize() {
		return OpenSslUtils::cipherIvLength($this->algorithm);
	}
	
	/**
	 * Deterined the key size using @see https://wiki.openssl.org/index.php/Manual:Enc(1)
	 */
	public function getKeySize() {
		switch ($this->algorithm) {
			case self::ALGORITHM_AES_128_CBC:
			case self::ALGORITHM_AES_128_CCM:
			case self::ALGORITHM_AES_128_CFB:
			case self::ALGORITHM_AES_128_CFB1:
			case self::ALGORITHM_AES_128_CFB8:
			case self::ALGORITHM_AES_128_CTR:
			case self::ALGORITHM_AES_128_ECB:
			case self::ALGORITHM_AES_128_GCM:
			case self::ALGORITHM_AES_128_OFB:
			case self::ALGORITHM_AES_128_XTS:
			case self::ALGORITHM_CAMELLIA_128_CBC:
			case self::ALGORITHM_CAMELLIA_128_CFB:
			case self::ALGORITHM_CAMELLIA_128_CFB1:
			case self::ALGORITHM_CAMELLIA_128_CFB8:
			case self::ALGORITHM_CAMELLIA_128_ECB:
			case self::ALGORITHM_CAMELLIA_128_OFB:
			case self::ALGORITHM_ID_AES128_CCM:
			case self::ALGORITHM_ID_AES128_GCM:
			case self::ALGORITHM_ID_AES128_WRAP:
			//@see: https://wiki.openssl.org/index.php/Manual:Enc(1)
			//->Blowfish and RC5 algorithms use a 128 bit key. 
			case self::ALGORITHM_BF_CBC:
			case self::ALGORITHM_BF_CFB:
			case self::ALGORITHM_BF_ECB:
			case self::ALGORITHM_BF_OFB:
			//@see http://www.gnu.org/software/gnu-crypto/manual/api/gnu/crypto/cipher/Cast5.html
			//-> since the CAST5 key schedule assumes an input key of 128 bits
			case self::ALGORITHM_CAST5_CBC:
			case self::ALGORITHM_CAST5_CFB:
			case self::ALGORITHM_CAST5_ECB:
			case self::ALGORITHM_CAST5_OFB:
			//@see https://en.wikipedia.org/wiki/International_Data_Encryption_Algorithm
			case self::ALGORITHM_IDEA_CBC:
			case self::ALGORITHM_IDEA_CFB:
			case self::ALGORITHM_IDEA_ECB:
			case self::ALGORITHM_IDEA_OFB:
			//@see https://wiki.openssl.org/index.php/Manual:Enc(1)
			case self::ALGORITHM_RC2_CBC:
			case self::ALGORITHM_RC2_CFB:
			case self::ALGORITHM_RC2_ECB:
			case self::ALGORITHM_RC2_OFB:
			case self::ALGORITHM_RC4:
			case self::ALGORITHM_SEED_CBC:
			case self::ALGORITHM_SEED_CFB:
			case self::ALGORITHM_SEED_ECB:
			case self::ALGORITHM_SEED_OFB:
				return 16;
				
			//@asee https://www.tutorialspoint.com/cryptography/triple_des.htm
			case self::ALGORITHM_DES_EDE:
			case self::ALGORITHM_DES_EDE_CBC:
			case self::ALGORITHM_DES_EDE_CFB:
			case self::ALGORITHM_DES_EDE_OFB:
				return 14;
			
			case self::ALGORITHM_AES_192_CBC:
			case self::ALGORITHM_AES_192_CCM:
			case self::ALGORITHM_AES_192_CFB:
			case self::ALGORITHM_AES_192_CFB1:
			case self::ALGORITHM_AES_192_CFB8:
			case self::ALGORITHM_AES_192_CTR:
			case self::ALGORITHM_AES_192_ECB:
			case self::ALGORITHM_AES_192_GCM:
			case self::ALGORITHM_AES_192_OFB:
			case self::ALGORITHM_CAMELLIA_192_CBC:
			case self::ALGORITHM_CAMELLIA_192_CFB:
			case self::ALGORITHM_CAMELLIA_192_CFB1:
			case self::ALGORITHM_CAMELLIA_192_CFB8:
			case self::ALGORITHM_CAMELLIA_192_ECB:
			case self::ALGORITHM_CAMELLIA_192_OFB:
			case self::ALGORITHM_ID_AES192_CCM:
			case self::ALGORITHM_ID_AES192_GCM:
			case self::ALGORITHM_ID_AES192_WRAP:
				return 24;
			
			//@asee https://www.tutorialspoint.com/cryptography/triple_des.htm
			case self::ALGORITHM_DES_EDE3:
			case self::ALGORITHM_DES_EDE3_CBC:
			case self::ALGORITHM_DES_EDE3_CFB:
			case self::ALGORITHM_DES_EDE3_CFB1:
			case self::ALGORITHM_DES_EDE3_CFB8:
			case self::ALGORITHM_DES_EDE3_OFB:
				return 21;
				
			
			case self::ALGORITHM_AES_256_CBC:
			case self::ALGORITHM_AES_256_CFB:
			case self::ALGORITHM_AES_256_CCM:
			case self::ALGORITHM_AES_256_CFB1:
			case self::ALGORITHM_AES_256_CFB8:
			case self::ALGORITHM_AES_256_CTR:
			case self::ALGORITHM_AES_256_ECB:
			case self::ALGORITHM_AES_256_GCM:
			case self::ALGORITHM_AES_256_OFB:
			case self::ALGORITHM_AES_256_XTS:
			case self::ALGORITHM_CAMELLIA_256_CB:
			case self::ALGORITHM_CAMELLIA_256_CFB:
			case self::ALGORITHM_CAMELLIA_256_CFB1:
			case self::ALGORITHM_CAMELLIA_256_CFB8:
			case self::ALGORITHM_CAMELLIA_256_ECB:
			case self::ALGORITHM_CAMELLIA_256_OFB:
			case self::ALGORITHM_ID_AES256_CCM:
			case self::ALGORITHM_ID_AES256_GCM:
			case self::ALGORITHM_ID_AES256_WRAP:
				return 32;
			
			
			//@see https://en.wikipedia.org/wiki/Data_Encryption_Standard
			case self::ALGORITHM_DES_CBC:
			case self::ALGORITHM_DES_CFB:
			case self::ALGORITHM_DES_CFB1:
			case self::ALGORITHM_DES_CFB8:
			case self::ALGORITHM_DES_ECB:
			case self::ALGORITHM_DES_OFB:
			//@see https://en.wikipedia.org/wiki/DES-X
			case self::ALGORITHM_DESX_CBC:
				return 7;
			

			case self::ALGORITHM_RC2_40_CBC:
 			case self::ALGORITHM_RC4_40:
				return 5;
				
 			case self::ALGORITHM_RC2_64_CBC:
 				return 8;
 			
			return 128;
		}
	}
	
	public static function isAlgorithmAvailable($algorithm) {
		return in_array($algorithm, self::getAvailableAlgorithms());
	}
	
	public static function getAvailableAlgorithms() {
		return openssl_get_cipher_methods();
	}
}
