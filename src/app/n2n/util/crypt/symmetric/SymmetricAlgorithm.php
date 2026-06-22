<?php
namespace n2n\util\crypt\symmetric;

enum SymmetricAlgorithm: string {
	case AES_128_GCM = 'aes-128-gcm';
	case AES_192_GCM = 'aes-192-gcm';
	case AES_256_GCM = 'aes-256-gcm';
}
