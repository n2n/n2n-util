<?php

namespace n2n\util\io;

use n2n\util\io\fs\FsUtils;
use PHPUnit\Framework\TestCase;

class FsUtilsTest extends TestCase {

	public function testPrettyFileSizeBytes(): void {
		$this->assertEquals('0 B', FsUtils::prettyFileSize(0));
		$this->assertEquals('1 B', FsUtils::prettyFileSize(1));
		$this->assertEquals('512 B', FsUtils::prettyFileSize(512));
		$this->assertEquals('1023 B', FsUtils::prettyFileSize(FsUtils::BYTES_PER_KB - 1));
	}

	public function testPrettyFileSizeKilobytes(): void {
		$this->assertEquals('1 KB', FsUtils::prettyFileSize(FsUtils::BYTES_PER_KB));
		$this->assertEquals('1.5 KB', FsUtils::prettyFileSize((int) (1.5 * FsUtils::BYTES_PER_KB)));
		$this->assertEquals('512 KB', FsUtils::prettyFileSize(512 * FsUtils::BYTES_PER_KB));
		$this->assertEquals('1023.99 KB', FsUtils::prettyFileSize(FsUtils::BYTES_PER_MB - 11));
	}

	public function testPrettyFileSizeMegabytes(): void {
		$this->assertEquals('1 MB', FsUtils::prettyFileSize(FsUtils::BYTES_PER_MB));
		$this->assertEquals('1.5 MB', FsUtils::prettyFileSize((int) (1.5 * FsUtils::BYTES_PER_MB)));
		$this->assertEquals('512 MB', FsUtils::prettyFileSize(512 * FsUtils::BYTES_PER_MB));
		$this->assertEquals('1023.99 MB', FsUtils::prettyFileSize(FsUtils::BYTES_PER_GB - 11777));
	}

	public function testPrettyFileSizeGigabytes(): void {
		$this->assertEquals('1 GB', FsUtils::prettyFileSize(FsUtils::BYTES_PER_GB));
		$this->assertEquals('1.5 GB', FsUtils::prettyFileSize((int) (1.5 * FsUtils::BYTES_PER_GB)));
		$this->assertEquals('10 GB', FsUtils::prettyFileSize(10 * FsUtils::BYTES_PER_GB));
		$this->assertEquals('100 GB', FsUtils::prettyFileSize(100 * FsUtils::BYTES_PER_GB));
	}
}

