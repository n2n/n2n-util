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
namespace n2n\util\io\fs;

use n2n\util\io\IoUtils;
use n2n\util\ex\IllegalStateException;
use n2n\util\io\IoException;

class FsPath {
	private $path;
	private $pathInfo;
	
	/**
	 * 
	 * @param string $path
	 */
	public function __construct($path) {
		$this->path = (string) $path;
		$this->pathInfo = pathinfo($path);
	}
	
	public function isAbsolute() {
		return (boolean) preg_match('#^([A-Za-z]:)?[/\\\\]+#', $this->path);
	}

// 	public function setPermissionMode($permissionMode) {
// 		switch ($permissionMode) {
// 			case self::PERMISSION_PRIVATE:
// 				$this->permissionMode = $permissionMode;
// 				$this->dirPermission = N2N::getProperties()->getFilesPrivateDirPermission();
// 				$this->filePermission = N2N::getProperties()->getFilesPrivateDirPermission();
// 				break;
// 			case self::PERMISSION_PUBLIC:
// 				$this->permissionMode = $permissionMode;
// 				$this->dirPermission = N2N::getProperties()->getFilesPublicDirPermission();
// 				$this->filePermission = N2N::getProperties()->getFilesPublicDirPermission();
// 				break;
// 			default:
// 				throw new NN6FileRuntimeException('invalid permission mode ' . $permissionMode);
// 		}
// 	}
	/**
	 * Returns the name of the file or directory denoted by this abstract pathname.
	 *
	 * @return bool The name of the file or directory denoted by this abstract pathname
	 */
	public function getName() {
		return $this->pathInfo['basename'];
	}
	/**
	 * 
	 * @return string
	 */
	public function getFileName() {
		if (!isset($this->pathInfo['filename'])) return null;
		return $this->pathInfo['filename'];
	}
	/**
	 * 
	 * @return string
	 */
	public function getExtension() {
		if (!isset($this->pathInfo['extension'])) return null;
		return $this->pathInfo['extension'];
	}
	/**
	 * Tests whether the file or directory denoted by this abstract pathname exists.
	 *
	 * @return bool true if and only if the file or directory denoted by this abstract pathname exists; false otherwise
	 */
	public function exists() {
		return file_exists($this->path);
	}
	/**
	 * 
	 * @return boolean
	 */
	public function isReadable() {
		return is_readable($this->path);
	}
	/**
	 * 
	 * @return boolean
	 */
	public function isWritable() {
		return is_writable($this->path);
	}
	/**
	 * 
	 * @return boolean
	 */
	public function isDir() {
		return is_dir($this->path);
	}
	/**
	 * 
	 * @return boolean
	 */
	public function isFile() {
		return is_file($this->path);
	}
	/**
	 * 
	 */
	public function touch() {
		return IoUtils::touch($this->path);
	}
	/**
	 * 
	 */
	public function delete() {
		if ($this->isFile()) {
			IoUtils::unlink($this->path);
		} else if ($this->isDir()) {
			IoUtils::rmdirs($this->path);
		}
	}
	/**
	 * 
	 * @param string $perm
	 */
	public function mkdirs(int|string|null $perm = null): void {
		if ($this->isDir()) return;

		try {
			IoUtils::mkdirs($this->path, $perm);
		} catch (FileOperationException $e) {
			// php is not thread safe. if the directory was created in the time after $this->isDir() and
			// IoUtils::mkdirs() an unwanted FileOperationException would be thrown with message 'File exists'.
			if ($this->isDir()) {
				return;
			}

			throw $e;
		}
	}
	/**
	 * Returns the abstract pathname of this abstract pathname's parent, or null if this pathname does not name a parent directory.
	 *
	 * @return \n2n\util\io\fs\FsPath
	 */
	public function getParent() {
		return new FsPath($this->pathInfo['dirname']);
	}
	/**
	 * Atomically creates a new, empty file named by this abstract path if and only if a file with this name does not yet exist.
	 *
	 * @return bool true if the named file does not exist and was successfully created; false if the named file already exists
	 */
	public function createFile(int|string|null $perm = null) {
		if ($this->isFile()) return false;
		
		IoUtils::touch($this->path);

		if ($perm !== null) {
			IoUtils::chmod($this->path, $perm);
		}

		return true;
	}

	/**
	 *
	 * @param int|string|null $dirPerm
	 * @param int|string|null $filePerm
	 * @return boolean
	 * @throws FileOperationException
	 */
	public function mkdirsAndCreateFile(int|string|null $dirPerm, int|string|null $filePerm) {
		if ($this->isFile()) return false;
		$parentDir = $this->getParent();
		
		if (!$parentDir->isDir()) {
			$parentDir->mkdirs($dirPerm);
		}
		
		return $this->createFile($filePerm);
	}
	
	public function isEmpty(): bool {
		if ($this->isFile()) {
			return $this->getSize() == 0;
		}
		
		$handle = IoUtils::opendir($this->path);
		while (false !== ($entry = readdir($handle))) {
			if ($entry == '.' && $entry == '..') continue;

			closedir($handle);
			return false;
		}
		
		closedir($handle);
		return true;
	}
	
	/**
	 * 
	 * @param string $pattern
	 * @return \n2n\util\io\fs\FsPath[]
	 */
	public function getChildren($pattern = '*') {
		$children = array();
		foreach (IoUtils::glob($this->path . DIRECTORY_SEPARATOR . $pattern)  as $path) {
			$children[] = new FsPath($path);
		}
		return $children;
	}
	
	
	
	/**
	 * 
	 */
	public function getDecendents($pattern = '*') {
		$decendents = $this->getChildren($pattern);
		$fileInfosIter = new \RecursiveIteratorIterator(new \RecursiveDirectoryIterator($this->path, \FilesystemIterator::SKIP_DOTS), \RecursiveIteratorIterator::SELF_FIRST);
		foreach($fileInfosIter as $path => $fileInfo) {
			if (!is_dir($path)) continue;
			
			$dirPath = new FsPath($path);
			$decendents = array_merge($decendents, $dirPath->getChildren($pattern));
		}
		
		return $decendents;
	}
	/**
	 * 
	 * @param string $pattern
	 * @return array<FsPath>
	 */
	public function getChildDirectories($pattern = '*') {
		$children = array();
		foreach (IoUtils::glob($this->path . DIRECTORY_SEPARATOR . $pattern, GLOB_ONLYDIR)  as $path) {
			$children[] = new FsPath($path);
		}
		return $children;
	}
	/**
	 * @param string $name
	 * @return boolean
	 */
	public function containsChildDirectory($name) {
		return is_dir($this->path . DIRECTORY_SEPARATOR . $name);
	}
	/**
	 * @param string $name
	 * @return boolean
	 */
	public function containsChildFile($name) {
		return is_file($this->path . DIRECTORY_SEPARATOR . $name);
	}
	/**
	 * 
	 * @return int
	 */
	public function getSize() {
		if ($this->isDir()) {
			return $this->calcDirSize();
		}
		
		return IoUtils::filesize($this->path);
	}
	/**
	 * 
	 * @return int
	 */
	private function calcDirSize() {
		$size = 0;
		foreach (new \RecursiveIteratorIterator(new \RecursiveDirectoryIterator($this->path)) as $file){
			$size += $file->getSize();
		}
		return $size;
	}
	/**
	 * 
	 * @return string
	 */
	public function __toString(): string {
		return $this->path;
	}
	/**
	 * 
	 * @param string $fsPath
	 * @param string $overwrite
	 * @throws FileAlreadyExistsException
	 * @return $path;
	 */
	private function prepareFileFsPath($fsPath, $overwrite) {
		$fsPath = (string) $fsPath;
		
		if (is_file($fsPath) && !$overwrite) {
			throw new FileAlreadyExistsException('File already exists: ' . $fsPath);
		}
		
		return $fsPath;
	}

	/**
	 *
	 * @param string $fsPath
	 * @param int|string|null $filePerm
	 * @param bool $overwrite
	 * @return FsPath
	 * @throws FileAlreadyExistsException
	 * @throws FileOperationException
	 * @throws IoException
	 */
	public function moveFile($fsPath, int|string|null $filePerm = null, bool $overwrite = false): FsPath {
		$fsPath = $this->prepareFileFsPath($fsPath, $overwrite);
		if (is_file($fsPath)) {
			IoUtils::unlink($fsPath);
		}
		
		IoUtils::rename($this->path, $fsPath);
		if ($filePerm !== null) {
			IoUtils::chmod($fsPath, $filePerm);
		}
		return new FsPath(IoUtils::realpath($fsPath));
	}
	/**
	 * 
	 * @param string $fsPath
	 * @param string $filePerm
	 * @param bool $overwrite
	 * @throws \n2n\util\io\IoException
	 * @return \n2n\util\io\fs\FsPath
	 */
	public function copyFile(FsPath $fsPath, int|string|null $filePerm = null, bool $overwrite = false) {
		if (!$this->isFile()) {
			throw new IllegalStateException('This is no file: ' . $this->path);
		}
		
		$fsPath = self::create($fsPath);
		
		if (!$overwrite && $fsPath->exists()) {
			throw new FileAlreadyExistsException('Destination file already exists: ' . $fsPath);	
		} 
		
		if ($fsPath->isDir()) {
			throw new FileOperationException('Destination path is a directory: ' . $fsPath);
		}
		
		IoUtils::copy($this->path, $fsPath);
		if ($filePerm !== null) {
			IoUtils::chmod($fsPath, $filePerm);
		}
		
		return new FsPath(IoUtils::realpath($fsPath));
	}

	/**
	 * @throws IoException
	 * @throws FileOperationException
	 */
	public function copy($fsPath, int|string|null $dirPerm = null, int|string|null $filePerm = null, $overwrite = false) {
		$fsPath = FsPath::create($fsPath);
		
		if ($this->isFile()) {
			return $this->copyFile($fsPath, $filePerm, $overwrite);
		}
		
		if ($fsPath->isFile()) {
			throw new FileOperationException('Can not copy \'' . $this->path 
					. '\'. Destination path points to file: ' . $fsPath);
		}
		
		if (!$fsPath->exists()) {
			IoUtils::mkdirs($fsPath, $dirPerm);
		}
		
		foreach ($this->getChildren() as $childPath) {
			$childPath->copy($fsPath->ext($childPath->getName()), $dirPerm, $filePerm, $overwrite);
		}

		return $fsPath;
	}

	/**
	 * @throws FileOperationException
	 */
	public function getMTime(): string {
	    return IoUtils::filemtime($this->path);
	}

	/**
	 * @throws FileOperationException
	 */
	public function getLastMod(): \DateTime {
		$mDateTime = new \DateTime();
		$mDateTime->setTimestamp(IoUtils::filemtime($this->path));
		return $mDateTime;
	}
	/**
	 * @param mixed $pathExt array or string
	 * @return \n2n\util\io\fs\FsPath
	 */
	public function createExtended($pathExt) {
		$newPath = rtrim($this->path, '\\/');
		if (is_array($pathExt)) {
			$newPath .= DIRECTORY_SEPARATOR . implode(DIRECTORY_SEPARATOR, $pathExt);
		} else {
			$newPath .= DIRECTORY_SEPARATOR . ltrim((string) $pathExt, '\\/');
		}
		
		return new FsPath($newPath);
	}
	/**
	 * @param @param mixed $pathExt array or string
	 * @return \n2n\util\io\fs\FsPath
	 */
	public function ext($pathExt) {
		return $this->createExtended($pathExt);
	}

	/**
	 * @throws FileOperationException
	 */
	public function chmod(string|int $perm, bool $omitNoSuchFileOrDirectoryError = false): void {
		try {
			IoUtils::chmod($this->path, $perm);
		} catch (FileOperationException $e) {
			if ($omitNoSuchFileOrDirectoryError && !$this->exists()) {
				return;
			}

			throw $e;
		}
	}
	
	public function equals($obj) {
		return $obj instanceof FsPath && $obj->__toString() === $this->__toString();
	}
	
	public static function create($expression) {
		if ($expression instanceof FsPath) {
			return $expression;
		}
		
		if (is_array($expression)) {
			$expression = implode(DIRECTORY_SEPARATOR, $expression);
		}
		
		return new FsPath($expression);
	}
}
