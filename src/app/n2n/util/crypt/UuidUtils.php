<?php

namespace n2n\util\crypt;
class UuidUtils {


	/**
	 * @deprecated use {@link TokenUtils::uuidv4()} instead
	 *
	 * @param string|null $data
	 * @return string
	 */
	static function uuidv4(?string $data = null): string {
		return TokenUtils::uuidv4($data);
	}

}