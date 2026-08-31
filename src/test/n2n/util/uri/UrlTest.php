<?php

namespace n2n\util\uri;

use PHPUnit\Framework\TestCase;

class UrlTest extends TestCase {
	function testCreateAndAccessComponents(): void {
		$url = Url::create('https://user:password@example.test:8443/a/b?foo=bar#section');

		$this->assertSame('https', $url->getScheme());
		$this->assertSame('user', $url->getAuthority()->getUser());
		$this->assertSame('password', $url->getAuthority()->getPassword());
		$this->assertSame('example.test', $url->getAuthority()->getHost());
		$this->assertSame(8443, $url->getAuthority()->getPort());
		$this->assertSame(['a', 'b'], $url->getPath()->getPathParts());
		$this->assertSame('bar', $url->getQuery()->get('foo'));
		$this->assertSame('section', $url->getFragment());
		$this->assertSame('https://user:password@example.test:8443/a/b?foo=bar#section', (string) $url);
	}

	function testChangesKeepUntouchedComponents(): void {
		$url = Url::create('https://example.test/a?foo=bar#old')
				->chHost('other.test')
				->pathExt('b')
				->queryExt(['baz' => 'qux'])
				->chFragment('new');

		$this->assertSame('https://other.test/a/b?baz=qux&foo=bar#new', (string) $url);
	}

	function testChangeUserInfoKeepsUntouchedComponents(): void {
		$url = Url::create('https://example.test:8443/a?foo=bar#section')->chUserInfo('user:password');

		$this->assertSame('https://user:password@example.test:8443/a?foo=bar#section', (string) $url);
		$this->assertSame('user', $url->getAuthority()->getUser());
		$this->assertSame('password', $url->getAuthority()->getPassword());
	}

	function testEncodedUserInfoRemainsStable(): void {
		$url = Url::create('https://user%20name:p%40ss@example.test');

		$this->assertSame('https://user%20name:p%40ss@example.test', (string) $url);
		$this->assertSame('user name', $url->getAuthority()->getUser());
		$this->assertSame('p@ss', $url->getAuthority()->getPassword());
		$this->assertSame('https://user%20name:p%40ss@example.test/a', (string) $url->chPath('/a'));
	}
}