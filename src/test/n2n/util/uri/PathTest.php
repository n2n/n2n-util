<?php
namespace n2n\util\uri;

use PHPUnit\Framework\TestCase;

class PathTest extends TestCase  {

	function testExt() {
		$urlableElementMock = $this->createMock(UrlableElement::class);
		$urlableElementMock->expects($this->once())->method('urlify')
				->willReturn('urlified-str');

		$p = new Path([]);

		$this->assertEquals(['str', 'hoi', 'urlified-str'],
				$p->ext('str', new Path(['hoi']), $urlableElementMock)->getPathParts());
	}

}