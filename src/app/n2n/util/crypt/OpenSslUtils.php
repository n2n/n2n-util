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

use n2n\util\crypt\ex\RandomPseudoBytesException;
use n2n\util\crypt\ex\CipherIvLengthException;
use n2n\util\crypt\ex\DecryptionFailedException;
use n2n\util\crypt\ex\EncryptionFailedException;

class OpenSslUtils {
	public static function cipherIvLength(string $algorithm): int {
		$res = @openssl_cipher_iv_length($algorithm);
		if ($res === false && $err = error_get_last()) {
			throw new CipherIvLengthException($err['message']);
		}
		return $res;
	}

	public static function randomPseudoBytes(int $size): string {
		$res = @openssl_random_pseudo_bytes($size);
		if ($res === false && $err = error_get_last()) {
			throw new RandomPseudoBytesException($err['message']);
		}
		return $res;
	}

	/**
	 * @param string $data
	 * @param string $method
	 * @param string $key
	 * @param int $options
	 * @param string $iv Pass nonce for AEAD ciphers.
	 * @param string|null &$tag Passed by reference. Required for AEAD ciphers.
	 * @param string $aad
	 * @param int $tagLength
	 * @return string
	 */
	public static function encrypt(string $data, string $method, string $key, int $options = 0,
			string $iv = '', ?string &$tag = null, string $aad = '', int $tagLength = 16): string {
		$res = @openssl_encrypt($data, $method, $key, $options, $iv, $tag, $aad, $tagLength);
		if ($res === false) {
			$err = error_get_last();
			throw new EncryptionFailedException($err['message'] ?? 'Encryption failed.');
		}
		return $res;
	}

	public static function decrypt(string $data, string $method, string $key, int $options = 0,
			string $iv = '', ?string $tag = null, string $aad = ''): string {
		$res = @openssl_decrypt($data, $method, $key, $options, $iv, $tag, $aad);
		if ($res === false) {
			$err = error_get_last();
			throw new DecryptionFailedException($err['message'] ?? 'Decryption failed.');
		}
		return $res;
	}
}
