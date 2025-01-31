<?php
namespace n2n\util\io;

use n2n\util\io\fs\FileOperationException;
use PHPUnit\Framework\TestCase;
use n2n\util\ex\err\impl\WarningError;

class IoUtilsTest extends TestCase {
	const TEST_DIR = __DIR__ . '/testFiles';
	const TEST_FILE = self::TEST_DIR . '/' . 'testfile';
	const TEST_FILE_DATA = 'testdata';
	const TEST_IMAGE_DIR = __DIR__ . '/testImages/';
	const TEST_PNG = self::TEST_IMAGE_DIR . '/img.png';
	const TEST_PDF = self::TEST_IMAGE_DIR . '/doc.pdf';
	const TEST_JPEG = self::TEST_IMAGE_DIR . '/img.jpg';
	const TEST_JPEG_24 = self::TEST_IMAGE_DIR . '/2x4.jpg';
	const TEST_WEBP = self::TEST_IMAGE_DIR . '/img.webp';
	const TEST_GIF = self::TEST_IMAGE_DIR . '/img.gif';

	protected function setUp(): void {
		$this->recursiveRmDir(self::TEST_DIR);
		mkdir(self::TEST_DIR);
		touch(self::TEST_FILE);
		file_put_contents(self::TEST_FILE, self::TEST_FILE_DATA);
	}

	protected function tearDown(): void {
		$this->recursiveRmDir(self::TEST_DIR);
	}

	public function testRename() {
		$oldPath = self::TEST_FILE;
		$newPath = self::TEST_DIR . '/testfileRenamed';
		touch($oldPath);
		IoUtils::rename($oldPath, $newPath);
		$this->assertFalse(is_file($oldPath));
		$this->assertTrue(is_file($newPath));
	}

	public function testRenameFileNotFound() {
		$this->expectException(FileOperationException::class);
		IoUtils::rename('asdf', 'asdf');
	}

	public function testMkdir() {
		$newDir = self::TEST_DIR . '/dir1/dir2';
		IoUtils::mkdirs($newDir, 0777);
		$this->assertTrue(is_dir($newDir));
	}

	public function testRmdir() {
		$dirToDelete = self::TEST_DIR . '/dirToDelete';
		IoUtils::mkdirs($dirToDelete, 777);
		IoUtils::rmdir($dirToDelete);
		$this->assertFalse(is_dir($dirToDelete));
	}

	public function testRmdirNotFound() {
		$this->expectException(FileOperationException::class);
		$dirToDelete = self::TEST_DIR . '/dirToDelete';
		IoUtils::rmdir($dirToDelete);
	}

	public function testRmdirs() {
		$newDir = self::TEST_DIR . '/dir1/dir2';
		IoUtils::mkdirs($newDir, 0777);
		IoUtils::rmdirs(self::TEST_DIR);
		$this->assertFalse(is_dir($newDir));
	}

	public function testOpendir() {
		$handle = IoUtils::opendir(self::TEST_DIR);
		$this->assertNotFalse($handle);
	}

	public function testOpendirNotFound() {
		$this->expectException(IoException::class);
		IoUtils::opendir('asdfyxv');
	}

	/**
	 * @throws IoException
	 */
	public function testPutContents() {
		$testData = 'testData';
		$testFile = self::TEST_DIR . '/testFileForPutContents';
		touch($testFile);
		IoUtils::putContents($testFile, $testData);
		$this->assertEquals($testData, file_get_contents($testFile));
	}

	/**
	 * @throws IoException
	 */
	public function testFileGetContents() {
		$contents =  IoUtils::getContents(self::TEST_FILE);
		$this->assertEquals(self::TEST_FILE_DATA, $contents);
	}

	/**
	 * @throws IoException
	 */
	public function testFileGetContentsNotFound() {
		$this->expectException(FileOperationException::class);
		try {
			IoUtils::getContents('asdf');
		} catch (FileOperationException $e) {
			$this->assertInstanceOf(WarningError::class, $e->getPrevious());
			$this->assertStringContainsString('No such file or directory', $e->getPrevious()->getMessage());
			throw $e;
		}

	}

	public function testFile() {
		$contents = IoUtils::file(self::TEST_FILE);
		$this->assertEquals($contents[0], self::TEST_FILE_DATA);
	}

	public function testFileNotFound() {
		$this->expectException(FileOperationException::class);
		IoUtils::file('asdf');
	}

	public function testCopy() {
		$targetPath = self::TEST_DIR . '/testFileCopy';
		IoUtils::copy(self::TEST_FILE, $targetPath);
		$this->assertEquals(file(self::TEST_FILE), file($targetPath));
	}

	public function testCopyNotFound() {
		$this->expectException(FileOperationException::class);
		IoUtils::copy('asdf', 'asdf');
	}

	public function testTouch() {
		$newFile = self::TEST_DIR . '/touchfiletest';
		IoUtils::touch($newFile);
		$this->assertTrue(is_file($newFile));
	}

	public function testFopen() {
		$result = IoUtils::fopen(self::TEST_FILE, 'r');
		$this->assertIsResource($result);
	}

	public function testFopenNotFound() {
		$this->expectException(IoException::class);
		IoUtils::fopen('asdf', 'r');
	}

	public function testStat() {
		$stat = IoUtils::stat(self::TEST_FILE);
		$this->assertTrue(is_array($stat));
	}

	public function testStatNotFound() {
		$this->expectException(FileOperationException::class);
		IoUtils::stat('asdf');
	}

	public function testFilesize() {
		$filesize = IoUtils::filesize(self::TEST_FILE);
		$this->assertIsNumeric($filesize);
	}

	public function testFilesizeNotFound() {
		$this->expectException(FileOperationException::class);
		IoUtils::filesize('asdf');
	}

	public function testStreamGetContents() {
		$contents = IoUtils::streamGetContents(fopen(self::TEST_FILE, 'r'));
		$this->assertEquals(self::TEST_FILE_DATA, $contents);
	}

	public function testFilemtime() {
		$filemtime = IoUtils::filemtime(self::TEST_FILE);
		$this->assertIsNumeric($filemtime);
	}

	public function testFilemtimeNotFound() {
		$this->expectException(FileOperationException::class);
		IoUtils::filemtime('asdf');
	}

	public function testUnlink() {
		IoUtils::unlink(self::TEST_FILE);
		$this->assertFalse(is_file(self::TEST_FILE));
	}

	public function testParseIniString() {
		$parsedIniString = IoUtils::parseIniString('[simple]
				val_one = "some value"
				val_two = 567
			
				[array]
				val_arr[] = "arr_elem_one"
				val_arr[] = "arr_elem_two"
				val_arr[] = "arr_elem_three"
			
				[array_keys]
				val_arr_two[6] = "key_6"
				val_arr_two[some_key] = "some_key_value"');
		$this->assertIsArray($parsedIniString);
		$this->assertEquals(4, count($parsedIniString));
	}

	public function testParseIniFile() {
		file_put_contents(self::TEST_FILE, '[simple]
				val_one = "some value"
				val_two = 567
			
				[array]
				val_arr[] = "arr_elem_one"
				val_arr[] = "arr_elem_two"
				val_arr[] = "arr_elem_three"

				[array_keys]
				val_arr_two[6] = "key_6"
				val_arr_two[some_key] = "some_key_value"');
		$parsedIniFile = IoUtils::parseIniFile(self::TEST_FILE);
		$this->assertIsArray($parsedIniFile);
		$this->assertEquals(4, count($parsedIniFile));
	}

	public function testParseIniFileNotFound() {
		$this->expectException(IoException::class);
		$parsedIniFile = IoUtils::parseIniFile('asdf');
	}

	public function testImageCreateFromPng() {
		$result = IoUtils::imageCreateFromPng(self::TEST_PNG);
		$this->assertTrue($result instanceof \GdImage);
	}

	public function testImageCreateFromPngWrongFormat() {
		$this->expectException(IoException::class);
		IoUtils::imageCreateFromPng(self::TEST_GIF);
	}

	public function testImageCreateFromGif() {
		$result = IoUtils::imageCreateFromGif(self::TEST_GIF);
		$this->assertTrue($result instanceof \GdImage);
	}

	public function testImageCreateFromGifWrongFormat() {
		$this->expectException(IoException::class);
		IoUtils::imageCreateFromGif(self::TEST_PNG);
	}

	public function testImageCreateFromJpeg() {
		$resource = IoUtils::imageCreateFromJpeg(self::TEST_JPEG);
		$this->assertTrue($resource instanceof \GdImage);
	}

	public function testImageCreateFromJpegWrongFormat() {
		$this->expectException(IoException::class);
		IoUtils::imageCreateFromJpeg(self::TEST_PNG);
	}

	public function testImageCreateFromWebp() {
		$result = IoUtils::imageCreateFromWebp(self::TEST_WEBP);
		$this->assertTrue($result instanceof \GdImage);
	}

	public function testImageCreateFromWebpWrongFormat() {
		$this->expectException(IoException::class);
		IoUtils::imageCreateFromJpeg(self::TEST_WEBP);
	}

	public function testImagePng() {
		$result = IoUtils::imagePng(imagecreatefrompng(self::TEST_PNG));
		$this->assertNotFalse($result);
	}

	public function testImageGif() {
		$result = IoUtils::imageGif(imagecreatefromgif(self::TEST_GIF));
		$this->assertNotFalse($result);
	}

	public function testImageJpeg() {
		$result = IoUtils::imageJpeg(imagecreatefromjpeg(self::TEST_JPEG));
		$this->assertNotFalse($result);
	}

	public function testImageWebp() {
		$result = IoUtils::imageWebp(imagecreatefromwebp(self::TEST_WEBP));
		$this->assertNotFalse($result);
	}

	public function testGetImageSize() {
		$sizeArr = IoUtils::getimagesize(self::TEST_PNG);
		$this->assertIsArray($sizeArr);
	}

	/**
	 * @throws IoException
	 */
	public function testImageRotate() {
		$image = IoUtils::imageCreateFromJpeg(self::TEST_JPEG_24);
		$this->assertEquals(4, imagesx($image));
		$this->assertEquals(2, imagesy($image));
		$image = IoUtils::imageRotate($image, 90);
		$this->assertEquals(2, imagesx($image));
		$this->assertEquals(4, imagesy($image));
	}

	/**
	 * @throws IoException
	 */
	function testImageRotateErr() {
		$this->expectException(IoException::class);
		$image = IoUtils::imageCreateFromJpeg(self::TEST_JPEG_24);
		IoUtils::imageRotate($image, 90, -1);
	}


	public function testGetImageSizePdf() {
		$this->expectException(IoException::class);
		IoUtils::getimagesize(self::TEST_PDF);
	}

	public function testValReturnException() {
		try {
			IoUtils::chmod('doesnt exist');
		} catch (\Throwable $e) {
			$this->assertInstanceOf(IoException::class, $e->getPrevious());
		}
	}

	private function recursiveRmDir($dir) {
		if (!is_dir($dir)) return;

		$objects = scandir($dir);
		foreach ($objects as $object) {
			if ($object != "." && $object != "..") {
				if (is_dir($dir. DIRECTORY_SEPARATOR .$object) && !is_link($dir."/".$object)) {
					$this->recursiveRmDir($dir . DIRECTORY_SEPARATOR . $object);
				} else {
					unlink($dir. DIRECTORY_SEPARATOR .$object);
				}
			}
		}
		rmdir($dir);
	}
}