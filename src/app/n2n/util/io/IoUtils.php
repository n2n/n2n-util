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
namespace n2n\util\io;

use n2n\util\io\fs\CouldNotAchieveFlockException;
use n2n\util\io\fs\FileOperationException;
use n2n\util\io\stream\impl\FileResourceStream;
use n2n\util\ex\ExUtils;

class IoUtils {

// 	public static function hasFsSpecialChars($string) {
// 		return false === strpbrk($string, '<>:"/\|?*') && ctype_print($string)
// 				&& !StringUtils::isEmpty($string);
// 	}
	/**
	 * strips and converts characters of a string so it can be enbedded into urls
	 * or file paths without any encoding nessesary
	 *
	 * @param string $string string to be cleaned
	 * @return string
	 */
	public static function stripSpecialChars(?string $string, bool $pretty = true): ?string {
		if (null === $string) {
			return null;
		}

		$string = trim($string);

		$unwanted = array('ä', 'à', 'â', 'ç', 'é', 'è', 'ê', 'î', 'ö', 'ß', 'ü',
				'Ä', 'À', 'Â', 'É', 'È', 'Ê', 'Î', 'Ö','Ü');
		$wanted = array('ae', 'a', 'a', 'c', 'e', 'e', 'e', 'i', 'oe', 'ss', 'ue',
				'Ae', 'A', 'A', 'E', 'E', 'E', 'I', 'Oe','Ue');
		$string = str_replace($unwanted, $wanted, $string);

		$string = preg_replace('/\s/s', '-', $string);
		$string = preg_replace('/[^0-9A-Za-z\\._-]/', '', $string);

		if ($pretty) {
			$string = preg_replace('/-{2,}/', '-', $string);
			$string = preg_replace('/-?\\.-?/', '.', $string);
			$string = preg_replace('/-?_-?/', '_', $string);
			$string = preg_replace('/\\.{2,}/', '.', $string);
			$string = preg_replace('/_{2,}/', '_', $string);
			$string = preg_replace('/[-,_\\.]+$/', '', $string);
			$string = preg_replace('/^[-,_\\.]+/', '', $string);
		}

		if ($string == '.') {
			$string = '';
		}

		return $string;
	}
	/**
	 *
	 * @param string $string
	 * @return bool
	 */
	public static function hasSpecialChars($string) {
		return ((bool) @preg_match('/[^0-9A-Za-z\\._-]/', $string))
					|| $string == '.' || $string == '..';

	}

	public static function hasStrictSpecialChars(string $string): bool {
		return preg_match('/\W/', $string);
	}

	/**
	 * @param string $string
	 * @return string
	 */
	public static function replaceStrictSpecialChars(string $string): string {
		return preg_replace('/\W/', '_', $string);

	}
	/**
	 *
	 * @param string $oldPath
	 * @param string $newPath
	 * @throws IoException
	 * @return bool
	 */
	public static function rename($oldPath, $newPath) {
		try {
			return self::valReturn(@rename($oldPath, $newPath));
		} catch(\Throwable $e) {
			throw new FileOperationException('Rename of \'' . $oldPath . '\' failed. Reason: ' . $e->getMessage(),
					null, $e);
		}
	}

	/**
	 * @param string $path
	 * @param int|string|null $permission keep possible umask restrictions in mind.
	 * @return bool
	 * @throws FileOperationException
	 */
	public static function mkdirs(string $path, int|string|null $permission = null): bool {
		if (is_string($permission)) {
			$permission = octdec($permission);
		} else if ($permission === null) {
			$permission = 0777;
		}

		try {
			return self::valReturn(@mkdir($path, $permission, true));
		} catch(\Throwable $e) {
			throw new FileOperationException('Mkdir of \'' . $path . '\' failed. Reason: ' . $e->getMessage(),
					null, $e);
		}
	}

	/**
	 * @param $path
	 * @throws FileOperationException
	 * @return bool
	 */
	public static function rmdir($path) {
		try {
			return self::valReturn(@rmdir($path));
		} catch(\Throwable $e) {
			throw new FileOperationException('Rmdir of \'' . $path . '\' failed. Reason: ' . $e->getMessage(),
					null, $e);
		}
	}
	/**
	 * @param string $path
	 * @throws IoException
	 */
	public static function rmdirs(string $path) {
		if (is_dir($path)) {
			try {
				$handle = self::valReturn(@opendir($path));
				while (false !== ($fileName = readdir($handle))) {
					if ($fileName == '.' || $fileName == '..') continue;

					self::rmdirs($path . DIRECTORY_SEPARATOR . $fileName);
				}

				closedir($handle);
			} catch(\Throwable $e) {
				throw new IoException('Opendir of \'' . $path . '\' failed. Reason: ' . $e->getMessage(), null, $e);
			}

			// @todo check requirements
			clearstatcache();
			IoUtils::rmdir($path);
		} else if (is_file($path)) {
			IoUtils::chmod($path, '0777');
			IoUtils::unlink($path);
		}
	}

	/**
	 * @param string $path
	 * @param resource $context
	 * @throws IoException
	 * @return boolean
	 */
	public static function opendir(string $path, $context = null) {
		try {
			if ($context === null) {
				return self::valReturn(@opendir($path));
			} else {
				return self::valReturn(@opendir($path, $context));
			}
		} catch(\Throwable $e) {
			throw new IoException('Opendir of \'' . $path . '\' failed. Reason: ' . $e->getMessage(), null, $e);
		}
	}

	/**
	 *
	 * @param string $pattern
	 * @param string $flags
	 * @return array
	 */
	public static function glob($pattern, $flags = 0) {
		$paths = self::valReturn(@glob($pattern, $flags));

		// Return array on false due to different behaviour on different systems
		if (!is_array($paths)) {
			return array();
		}

		return $paths;
	}
	/**
	 *
	 * @param string $path
	 * @param string $filePermission
	 * @throws IoException
	 * @deprecated use IoUtils::touch() and IoUtils::chmod()
	 */
	public static function createFile($path, $filePermission = null) {
		self::touch($path);
		if (isset($filePermission)) {
			self::chmod($path, $filePermission);
		}
	}
	/**
	 *
	 * @param string $path
	 * @param string $contents
	 * @param int $flags
	 * @param resource $context
	 * @throws IoException
	 * @return int|false
	 */
	public static function putContents(string $path, string $contents, int $flags = 0, $context = null) {
		try {
			return @file_put_contents($path, $contents, $flags, $context);
		} catch (\Throwable $e) {
			throw new FileOperationException('PutContents of \'' . $path . '\' failed. Reason: ' . $e->getMessage(),
					null, $e);
		}
	}
	/**
	 *
	 * @param string $path
	 * @return string|false
	 * @throws IoException
	 */
	public static function getContents(string $path) {
		try {
			return ExUtils::convertTriggeredErrors(fn () => file_get_contents($path));
		} catch (\Throwable $e) {
			throw new FileOperationException('GetContents of \'' . $path . '\' failed. Reason: ' . $e->getMessage(),
					null, $e);
		}
	}
	/**
	 *
	 * @param string $path
	 * @return string
	 * @throws IoException
	 */
	public static function file(string $path) {
		try {
			return ExUtils::convertTriggeredErrors(fn () => file($path));
		} catch (\Throwable $e) {
			throw new FileOperationException('File of \'' . $path . '\' failed. Reason: ' . $e->getMessage(),
					null, $e);
		}
	}
	/**
	 *
	 * @param string $path
	 * @param string $targetPath
	 * @throws IoException
	 * @return bool
	 */
	public static function copy($path, $targetPath, $context = null) {
		try {
			if ($context === null) {
				return ExUtils::convertTriggeredErrors(fn () => copy($path, $targetPath));
			} else {
				return ExUtils::convertTriggeredErrors(fn () => copy($path, $targetPath, $context));
			}
		} catch (\Throwable $e) {
			throw new FileOperationException('Copy of \'' . $path . '\' failed. Reason: ' . $e->getMessage(),
					null, $e);
		}
	}

	/**
	 *
	 * @param string $path
	 * @param int|string $filePermission octal string
	 * @return bool
	 * @throws FileOperationException
	 */
	public static function chmod(string $path, int|string $filePermission = 0777): bool {
		if (is_string($filePermission)) {
			$filePermission = octdec($filePermission);
		}

		try {
			return self::valReturn(@chmod($path, $filePermission));
		} catch (\Throwable $e) {
			throw new FileOperationException('Chmod of \'' . $path . '\' (permission: ' . $filePermission . ') 
					failed. Reason: ' . $e->getMessage(), null, $e);
		}
	}

	/**
	 * @return bool
	 * @todo add param time and atime
	 */
	public static function touch($filename) {
		try {
			return self::valReturn(@touch($filename));
		} catch (\Throwable $e) {
			throw new FileOperationException('Touch of \'' . $filename . '\' failed. Reason: ' . $e->getMessage(),
					null, $e);
		}
	}
	/**
	 *
	 * @param string $path
	 * @param string $mode
	 * @throws IoException
	 * @return resource
	 */
	public static function fopen(string $path, string $mode) {
		try {
			return self::valReturn(@fopen($path, $mode));
		} catch (\Throwable $e) {
			throw new IoException('Fopen of \'' . $path . '\' failed. Reason: ' . $e->getMessage(), null, $e);
		}
	}

	/**
	 * @param string $path
	 * @return array|false
	 * @throws FileOperationException
	 */
	public static function stat(string $path) {
		try {
			return self::valReturn(@stat($path));
		} catch (\Throwable $e) {
			throw new FileOperationException('Stat of \'' . $path . '\' failed. Reason: ' . $e->getMessage(),
					null, $e);
		}
	}

	/**
	 * @param string $path
	 * @return false|int
	 * @throws FileOperationException
	 */
	public static function filesize(string $path) {
		try {
			return self::valReturn(@filesize($path));
		} catch (\Throwable $e) {
			throw new FileOperationException('Filesize of \'' . $path . '\' failed. Reason: ' . $e->getMessage(),
					null, $e);
		}
	}

	/**
	 * @param string $path
	 * @return int
	 * @throws FileOperationException
	 */
	public static function readfile(string $path): int {
		try {
			return self::valReturn(@readfile($path));
		} catch (\Throwable $e) {
			throw new FileOperationException('Readfile of \'' . $path . '\' failed. Reason: ' . $e->getMessage(),
					null, $e);
		}
	}

	/**
	 * @param $handle
	 * @param string|null $string
	 * @return int
	 * @throws IoResourceException
	 */
	public static function fwrite($handle, ?string $string) {
		$num = self::valReturn(@fwrite($handle, (string) $string));
		if (false === $num) {
			throw new IoResourceException('Could not write to resource: ' . $handle);
		}

		return $num;
	}

	/**
	 * @param $handle
	 * @param int|null $length
	 * @return string
	 * @throws IoResourceException
	 */
	public static function fread($handle, ?int $length = null) {
		$str = self::valReturn(@fread($handle, $length));
		if (false === $str) {
			throw new IoResourceException('Could not read from file');
		}

		return $str;
	}

	/**
	 * @param $handle
	 * @param int|null $length
	 * @return string
	 * @throws IoResourceException
	 */
	public static function fgets($handle, ?int $length = null): string {
		return self::valReturn(@fgets($handle, $length));
	}

	/**
	 * @param $handle
	 * @param int $maxlength
	 * @param int $offset
	 * @return false|string
	 * @throws IoResourceException
	 */
	public static function streamGetContents($handle, int $maxlength = -1, int $offset = -1) {
		try {
			return self::valReturn(@stream_get_contents($handle, $maxlength, $offset));
		} catch (\Throwable $e) {
			throw new IoResourceException('Streamgetcontents failed. Reason: ' . $e->getMessage(), null, $e);
		}
	}

	/**
	 * @param string $filePath
	 * @return FileResourceStream
	 * @throws CouldNotAchieveFlockException
	 */
	public static function createSafeFileStream(string $filePath) {
		return new FileResourceStream($filePath, 'c+', LOCK_EX);
	}
	/**
	 *
	 * @return FileResourceStream
	 */
	public static function createSafeFileOutputStream(string $filePath) {
		return new FileResourceStream($filePath, 'w', LOCK_EX);
	}
	/**
	 *
	 * @param string $filePath
	 * @return FileResourceStream
	 */
	public static function createSafeFileInputStream(string $filePath) {
		return new FileResourceStream($filePath, 'r', LOCK_SH);
	}
	/**
	 *
	 * @param string $path
	 * @param string $contents
	 * @throws IoException
	 */
	public static function putContentsSafe(string $path, string $contents) {
		$fileOutputStream = self::createSafeFileOutputStream($path);
		$fileOutputStream->write($contents);
		$fileOutputStream->close();
	}
	/**
	 *
	 * @param string $path
	 * @return string
	 * @throws IoException
	 */
	public static function getContentsSafe($path) {
		$fileReader = self::createSafeFileInputStream($path);
		$contents = $fileReader->read();
		$fileReader->close();
		return $contents;
	}
	/**
	 *
	 * @param string $path
	 * @return string
	 * @throws FileOperationException
	 */
	public static function filemtime($path) {
		try {
			return self::valReturn(@filemtime($path));
		} catch (\Throwable $e) {
			throw new FileOperationException('Filemtime of \'' . $path . '\' failed. Reason: ' . $e->getMessage(),
					null, $e);
		}
	}
	/**
	 *
	 * @param string $path
	 * @throws FileOperationException
	 */
	public static function unlink($path) {
		try {
			return self::valReturn(@unlink($path));
		} catch (\Throwable $e) {
			throw new FileOperationException('Unlink of \'' . $path . '\' failed. Reason: ' . $e->getMessage(),
					null, $e);
		}
	}
	/**
	 *
	 * @param string $iniString
	 * @param string $processSections
	 * @param string $scannerMode
	 * @throws IoException
	 * @return array
	 */
	public static function parseIniString($iniString, $processSections = false, $scannerMode = null) {
		try {
			$result = self::valReturn(@parse_ini_string($iniString, $processSections, $scannerMode));
		} catch (\Throwable $e) {
			throw new IoException('ParseIniString of \'' . $iniString . '\' failed. Reason: ' . $e->getMessage(),
					null, $e);
		}

		return $result;
	}
	/**
	 *
	 * @param string $path
	 * @param bool $processSections
	 * @param int $scannerMode
	 * @throws IoException
	 * @return array
	 */
	public static function parseIniFile(string $path, bool $processSections = false, int $scannerMode = INI_SCANNER_NORMAL) {
		try {
			$result = self::valReturn(@parse_ini_file($path, $processSections, $scannerMode));
		} catch (\Throwable $e) {
			throw new IoException('ParseIniFile of \'' . $path . '\' failed. Reason: ' . $e->getMessage(), null, $e);
		}

		return $result;
	}

	/**
	 * @param $filePath
	 * @return false|\GdImage|resource
	 * @throws IoException
	 */
	public static function imageCreateFromPng($filePath) {
		$image = null;
		try {
			$image = self::valReturn(@imagecreatefrompng($filePath));
		} catch (\Throwable $e) {
			throw new IoException('Imagecreatefrompng of \'' . $filePath . '\' failed. Reason: ' . $e->getMessage(),
					null, $e);
		}

		return $image;
	}

	/**
	 * @param $filePath
	 * @return false|\GdImage|resource
	 * @throws IoException
	 */
	public static function imageCreateFromGif($filePath) {
		$image = null;
		try {
			$image = self::valReturn(@imagecreatefromgif($filePath));
		} catch (\Throwable $e) {
			throw new IoException('Imagecreatefromgif of \'' . $filePath . '\' failed. Reason: ' . $e->getMessage(),
					null, $e);
		}

		return $image;
	}

	/**
	 * @param $filePath
	 * @return false|\GdImage|resource
	 * @throws IoException
	 */
	public static function imageCreateFromJpeg($filePath) {
		$image = null;
		try {
			$image = self::valReturn(@imagecreatefromjpeg($filePath));
		} catch (\Throwable $e) {
			throw new IoException('Imagecreatefromjpeg of \'' . $filePath . '\' failed. Reason: ' . $e->getMessage(),
					null, $e);
		}

		return $image;
	}

	/**
	 * @param $filePath
	 * @return false|\GdImage|resource
	 * @throws IoException
	 */
	public static function imageCreateFromWebp($filePath) {
		$image = null;
		try {
			$image = self::valReturn(@imagecreatefromwebp($filePath));
		} catch (\Throwable $e) {
			throw new IoException('Imagecreatefromwebp of \'' . $filePath . '\' failed. Reason: ' . $e->getMessage(),
					null, $e);
		}

		return $image;
	}

	/**
	 * @param $resource
	 * @param null $filePath
	 * @param null $quality
	 * @param null $filters
	 * @return bool
	 * @throws IoException
	 */
	public static function imagePng($resource, $filePath = null, $quality = null, $filters = null) {
		try {
			return self::valReturn(@imagepng($resource, $filePath, $quality, $filters));
		} catch (\Throwable $e) {
			throw new IoException('Imagepng of \'' . $filePath . '\' failed. Reason: ' . $e->getMessage(), null, $e);
		}
	}

	/**
	 * @param $resource
	 * @param null $filePath
	 * @return bool
	 * @throws IoException
	 */
	public static function imageGif($resource, $filePath = null) {
		try {
			return self::valReturn(@imagegif($resource, $filePath));
		} catch (\Throwable $e) {
			throw new IoException('Imagegif of \'' . $filePath . '\' failed. Reason: ' . $e->getMessage(), null, $e);
		}
	}

	/**
	 * @param $resource
	 * @param null $filePath
	 * @param null $quality
	 * @return bool
	 * @throws IoException
	 */
	public static function imageJpeg($resource, $filePath = null, $quality = null) {
		try {
			return self::valReturn(@imagejpeg($resource, $filePath));
		} catch (\Throwable $e) {
			throw new IoException('Imagejpeg of \'' . $filePath . '\' failed. Reason: ' . $e->getMessage(), null, $e);
		}
	}

	/**
	 * @param $resource
	 * @param null $filePath
	 * @param null $quality
	 * @return bool
	 * @throws IoException
	 */
	public static function imageWebp($resource, $filePath = null, $quality = null) {
		try {
			return self::valReturn(@imagewebp($resource, $filePath));
		} catch (\Throwable $e) {
			throw new IoException('Imagejpeg of \'' . $filePath . '\' failed. Reason: ' . $e->getMessage(), null, $e);
		}
	}

	/**
	 *
	 * @param string $filePath
	 * @param int $operation
	 * @return Flock
	 */
	public static function createFlock($filePath, $operation, $requried = true) {
		try {
			return new Flock(self::fopen($filePath, 'c'), $operation);
		} catch (CouldNotAchieveFlockException $e) {
			if (!$requried) return null;
			throw $e;
		}
	}
	/**
	 *
	 * @param string $orgPath
	 * @throws InvalidPathException
	 */
	public static function realpath($orgPath, $fileRequired = null) {
		$path = self::valReturn(@realpath($orgPath));

		if ($fileRequired === true && !is_file($path)) {
			throw new InvalidPathException('Path points to non file:' . $path);
		}

		if ($fileRequired === false && !is_dir($path)) {
			throw new InvalidPathException('Path points to non directory: ' . $path);
		}

		return $path;
	}

	/**
	 * @param $handle
	 * @param $operation
	 * @param null $wouldblock
	 * @return bool
	 * @throws CouldNotAchieveFlockException
	 */
	public static function flock($handle, $operation, &$wouldblock = null) {
		try {
			self::valReturn(@flock($handle, $operation, $wouldblock));
		} catch (\Throwable $e) {
			throw new CouldNotAchieveFlockException('Could not achieve flock: ' . $operation, null, $e);
		}
		return true;
	}

	/**
	 * @param $handle
	 * @param $size
	 * @throws IoResourceException
	 */
	public static function ftruncate($handle, $size) {
		try {
			return self::valReturn(@ftruncate($handle, $size));
		} catch (\Throwable $e) {
			throw new IoResourceException('Could not truncate.', null, $e);
		}
	}

	/**
	 * @param $handle
	 * @param $offset
	 * @param int $whence
	 * @throws IoResourceException
	 */
	public static function fseek($handle, $offset, $whence = SEEK_SET) {
		try {
			return self::valReturn(@fseek($handle, $offset, $whence));
		} catch (\Throwable $e) {
			throw new IoResourceException('Could not seek. Offset: ' . $offset, null, $e);
		}
	}

	/**
	 * @param $handle
	 * @param $offset
	 * @param int $whence
	 * @return int
	 * @throws IoResourceException
	 */
	public static function ftell($handle): int {
		try {
			$offset = self::valReturn(@ftell($handle));
		} catch (\Throwable $e) {
			throw new IoResourceException('Could not tell.', null, $e);
		}

		return $offset;
	}

// 	public static function ensureFileIsAccessibleThroughHttp(Managable $file) {
// 		if ($file->getFileManager() instanceof HttpAccessible) return;
// 		throw new FileManagingException(
// 				SysTextUtils::get('n2n_error_io_file_is_not_accessible_through_http',
// 						array('file' => $file->getPath(), 'file_name' => $file->getOriginalName())));
// 	}

	/**
	 * @param string $filename
	 * @throws IoException
	 * @return array
	 */
	public static function getimagesize(string $filename) {
		$size = null;
		try {
			$size = self::valReturn(@getimagesize($filename));
		} catch (\Throwable $e) {
			throw new IoException('Getimagesize of \'' . $filename . '\' failed. Reason: ' . $e->getMessage(),
					null, $e);
		}

		return $size;
	}
	/**
	 * Returns a file size limit in bytes based on the PHP upload_max_filesize
	 * and post_max_size
	 * @return int
	 */
	public static function determineFileUploadMaxSize() {
		static $maxSize = -1;

		if ($maxSize < 0) {
			// Start with post_max_size.
			$maxSize = self::parsePhpIniSize(ini_get('post_max_size'));

			// If upload_max_size is less, then reduce. Except if upload_max_size is
			// zero, which indicates no limit.
			$upload_max = self::parsePhpIniSize(ini_get('upload_max_filesize'));
			if ($upload_max > 0 && $upload_max < $maxSize) {
				$maxSize = $upload_max;
			}
		}

		return $maxSize;
	}

	const DEFAULT_MEMORY_LIMIT = 33554432;

	/**
	 * @return int
	 */
	static function determinMemoryLimit() {
		static $memoryLimit = null;

		if ($memoryLimit !== null) {
			return $memoryLimit;
		}

		$memoryLimit = self::parsePhpIniSize(ini_get('memory_limit'));

		if (empty($memoryLimit)) {
			$memoryLimit = self::DEFAULT_MEMORY_LIMIT;
		}

		return $memoryLimit;
	}

	/**
	 * @param $ch
	 * @return bool|string
	 * @throws CurlOperationException
	 */
	public static function curlExec($ch) {
		try {
			$result = self::valReturn(@curl_exec($ch));
		} catch (\Throwable $e) {
			throw new CurlOperationException('Curlexec of \'' . $ch . '\' failed. Reason: ' . $e->getMessage(),
					null, $e);
		}

		curl_close($ch);
		return $result;
	}

	/**
	 * @param string $size
	 * @return float
	 */
	public static function parsePhpIniSize(string $size) {
		$unit = preg_replace('/[^bkmgtpezy]/i', '', $size); // Remove the non-unit characters from the size.
		$size = preg_replace('/[^0-9\.]/', '', $size); // Remove the non-numeric characters from the size.
		if ($unit) {
			// Find the position of the unit in the ordered string which is the power of magnitude to multiply a kilobyte by.
			return round($size * pow(1024, stripos('bkmgtpezy', $unit[0])));
		} else {
			return round($size);
		}
	}

	private static function valReturn($result) {
		if ($result !== false) {
			return $result;
		}

		$lastError = error_get_last();
		if ($lastError !== null) {
			throw new IoException($lastError['message']);
		}
		throw new IoException('method returns false');
	}

	/**
	 * @param \GdImage $image
	 * @param int $angle
	 * @param int $backgroundColor
	 * @return \GdImage
	 * @throws IoException
	 */
	public static function imageRotate(\GdImage $image, int $angle, int $backgroundColor = 0): \GdImage {
		try {
			$result = self::valReturn(imagerotate($image, $angle, $backgroundColor));
		} catch (\Throwable $e) {
			throw new IoException('Imagerotate failed. Reason: ' . $e->getMessage(),
					null, $e);
		}

		return $result;
	}
}