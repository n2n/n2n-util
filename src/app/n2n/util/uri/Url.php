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
namespace n2n\util\uri;

use n2n\util\type\ArgUtils;
use Psr\Http\Message\UriInterface;
use Psr\Http\Message\UriFactoryInterface;
use Stringable;

final class Url implements \JsonSerializable, Stringable {
	const SCHEME_SEPARATOR = ':';
	const AUTHORITY_PREFIX = '//';
	const PATH_PREFIX = Path::DELIMITER;
	const QUERY_PREFIX = '?';
	const FRAGMENT_PREFIX = '#';

	private ?string $scheme;
	private ?string $authority;
	private ?string $path;
	private ?string $query;

	/**
	 * According to RFC 2396, RFC 3986, and RFC 7320, the format of fragment identifiers depends on the media type.
	 * Therefore, this class leaves the fragment as it is and does not perform any encoding
	 *
	 * @var string|null
	 */
	private ?string $fragment;

	public function __construct(?string $scheme = null, ?Authority $authority = null, ?Path $path = null,
			?Query $query = null, ?string $fragment = null) {
		$this->scheme = ArgUtils::stringOrNull($scheme);
		$this->authority = $authority === null ? null : (string) $authority;
		$this->path = $path === null ? null : (string) $path;
		$this->query = $query === null ? null : (string) $query;
		$this->fragment = $fragment;
	}
	/**
	 * @return string|null
	 */
	public function getScheme(): ?string {
		return $this->scheme;
	}

	public function hasScheme(): bool {
		return null !== $this->scheme;
	}
	/**
	 * @return Authority
	 */
	public function getAuthority(): Authority {
		if ($this->authority === null) {
			return new Authority();
		}

		$authorityMap = parse_url(self::AUTHORITY_PREFIX . $this->authority);
		if ($authorityMap === false) {
			throw new \InvalidArgumentException('Invalid authority: ' . $this->authority);
		}
		return new Authority($authorityMap['host'] ?? null, $authorityMap['port'] ?? null,
				isset($authorityMap['user']) ? rawurldecode($authorityMap['user']) : null,
				isset($authorityMap['pass']) ? rawurldecode($authorityMap['pass']) : null);
	}
	/**
	 * @return Path
	 */
	public function getPath(): Path {
		if ($this->path === null) {
			return new Path(array());
		}
		return Path::create($this->path);
	}
	/**
	 * @return Query
	 */
	public function getQuery(): Query {
		if ($this->query === null) {
			return new Query(array());
		}
		return Query::create($this->query);
	}
	/**
	 * @return string|null
	 */
	public function getFragment(): ?string {
		return $this->fragment;
	}
	/**
	 * @param string $scheme
	 * @return \n2n\util\uri\Url
	 */
	public function chScheme(?string $scheme = null): Url {
		if ($scheme === $this->scheme) return $this;
		return new Url($scheme, $this->storedAuthority(), $this->storedPath(), $this->storedQuery(), $this->fragment);
	}
	/**
	 * @param mixed $authority
	 * @return \n2n\util\uri\Url
	 */
	public function chAuthority(mixed $authority): Url {
		$authority = Authority::create($authority);
		if ((string) $authority === $this->authority) return $this;
		return new Url($this->scheme, $authority, $this->storedPath(), $this->storedQuery(), $this->fragment);
	}
	/**
	 * @param mixed $userInfo
	 * @return \n2n\util\uri\Url
	 */
	public function chUserInfo(?string $userInfo): Url {
		$authority = $this->getAuthority();
		$userInfoParts = $userInfo === null ? array(null, null) : explode(':', $userInfo, 2);
		$user = $userInfoParts[0];
		$password = $userInfoParts[1] ?? null;
		if ($authority->getUser() === $user && $authority->getPassword() === $password) return $this;

		return new Url($this->scheme,
				new Authority($authority->getHost(), $authority->getPort(), $user, $password),
				$this->storedPath(), $this->storedQuery(), $this->fragment);
	}
	/**
	 * @param mixed $host
	 * @return \n2n\util\uri\Url
	 */
	public function chHost(?string $host = null): Url {
		if ($this->getAuthority()->getHost() === $host) return $this;
		return new Url($this->scheme, $this->getAuthority()->chHost($host), $this->storedPath(),
				$this->storedQuery(), $this->fragment);
	}
	/**
	 * @param mixed $port
	 * @return \n2n\util\uri\Url
	 */
	public function chPort(?int $port = null): Url {
		if ($this->getAuthority()->getPort() === $port) return $this;
		return new Url($this->scheme, $this->getAuthority()->chPort($port), $this->storedPath(),
				$this->storedQuery(), $this->fragment);
	}
	/**
	 * @param mixed $path
	 * @return \n2n\util\uri\Url
	 */
	public function chPath(string|Path|null $path = null): Url {
		$path = Path::create($path);
		if ((string) $path === $this->path) return $this;
		return new Url($this->scheme, $this->storedAuthority(), $path, $this->storedQuery(), $this->fragment);
	}
	
	/**
	 * @param bool $endingDelimitter
	 * @return \n2n\util\uri\Url
	 */
	function chPathEndingDelimiter(bool $endingDelimitter): Url {
		return $this->chPath($this->getPath()->chEndingDelimiter($endingDelimitter));
	}
	
	/**
	 * @param mixed $query
	 * @return \n2n\util\uri\Url
	 */
	public function chQuery(mixed $query): Url {
		$query = Query::create($query);
		if ((string) $query === $this->query) return $this;
		return new Url($this->scheme, $this->storedAuthority(), $this->storedPath(), $query, $this->fragment);
	}
	/**
	 * @param string $fragment
	 * @return \n2n\util\uri\Url
	 */
	public function chFragment(?string $fragment): Url {
		if ($fragment === $this->fragment) return $this;
		return new Url($this->scheme, $this->storedAuthority(), $this->storedPath(), $this->storedQuery(), $fragment);
	}

	public function ext(mixed $relativeUrl): Url {
		$relativeUrl = Url::build($relativeUrl);
		
		if ($relativeUrl === null) return $this;
		
		if (!$relativeUrl->isRelative()) {
			throw new \InvalidArgumentException('Passed url is not relative: ' . $relativeUrl);
		}

		return $this->extR($relativeUrl->getPath(), $relativeUrl->getQuery(), $relativeUrl->getFragment());
	}
	/**
	 * @param mixed $pathExtEnc
	 * @param mixed $query
	 * @param mixed $fragment
	 * @return \n2n\util\uri\Url
	 */
	public function extR(mixed $pathExt = null, mixed $queryExt = null, ?string $fragment = null): Url {
		if ($pathExt === null && $queryExt === null && $fragment === null) return $this;

		return new Url($this->scheme, $this->storedAuthority(), $this->getPath()->ext($pathExt),
				$this->getQuery()->ext($queryExt),
			($fragment === null ? $this->fragment : $fragment));
	}

	/**
	 * @param mixed ...$pathPartExts
	 * @return \n2n\util\uri\Url
	 */
	public function pathExt(mixed ...$pathPartExts): Url {
		return new Url($this->scheme, $this->storedAuthority(), $this->getPath()->ext(...$pathPartExts),
				$this->storedQuery(), $this->fragment);
	}

	/**
	 * @param mixed ...$pathExts
	 * @return \n2n\util\uri\Url
	 */
	public function pathExtEnc(mixed ...$pathExts): Url {
		return new Url($this->scheme, $this->storedAuthority(), $this->getPath()->extEnc(...$pathExts),
				$this->storedQuery(), $this->fragment);
	}

	/**
	 * @param mixed $query
	 * @return \n2n\util\uri\Url
	 */
	public function queryExt(mixed $query): Url {
		return new Url($this->scheme, $this->storedAuthority(), $this->getPath(), $this->getQuery()->ext($query),
				$this->fragment);
	}

	/**
	 * @param number $num
	 * @return \n2n\util\uri\Url
	 */
	public function reducedPath(int $num = 1): Url {
		return new Url($this->scheme, $this->storedAuthority(), $this->getPath()->reduced($num),
				$this->storedQuery(), $this->fragment);
	}
	/**
	 * @param number $start
	 * @param string $num
	 * @return \n2n\util\uri\Url
	 */
	public function subPath(int $start, ?int $num = null): Url {
		return new Url($this->scheme, $this->storedAuthority(), $this->getPath()->sub($start, $num),
				$this->storedQuery(), $this->fragment);
	}
	/**
	 * @return \n2n\util\uri\Url
	 */
	public function toRelativeUrl(): Url {
		return new Url(null, null, $this->storedPath(), $this->storedQuery(), $this->fragment);
	}

	public static function createRelativeUrl(mixed $path = null, mixed $query = null,
			?string $fragment = null): Url {
		return new Url(null, null, Path::create($path), Query::create($query), $fragment);
	}

	/**
	 * @param $expression
	 * @return Url|null
	 */
	public static function build(mixed $expression, bool $lenient = false): ?Url {
		if ($expression === null || $expression instanceof Url) return $expression;

		return self::create($expression, $lenient);
	}

	/**
	 * @param mixed $expression
	 * @throws \InvalidArgumentException
	 * @return \n2n\util\uri\Url
	 */
	public static function create(mixed $expression, bool $lenient = false): Url {
		if ($expression instanceof Url) {
			return $expression;
		}

		if ($expression instanceof Authority) {
			return new Url(null, $expression);
		}

		if ($expression instanceof Path) {
			return new Url(null, null, $expression);
		}

		if (is_array($expression)) {
			return new Url(null, null, Path::create($expression));
		}

		if ($expression instanceof Query) {
			return new Url(null, null, null, $expression);
		}

		$uriMap = parse_url((string) $expression);
		if ($uriMap === false) {
			throw new \InvalidArgumentException('Invalid uri: ' . $expression);
		}

		$uri = new Url();
		if (isset($uriMap['scheme'])) {
			$uri->scheme = $uriMap['scheme'];
		}
		if (isset($uriMap['host']) || isset($uriMap['user'])) {
			$uri->authority = (string) new Authority($uriMap['host'] ?? null, $uriMap['port'] ?? null,
					isset($uriMap['user']) ? rawurldecode($uriMap['user']) : null,
					isset($uriMap['pass']) ? rawurldecode($uriMap['pass']) : null);
		}
		if (isset($uriMap['path'])) {
			$uri->path = (string) Path::create($uriMap['path'], $lenient);
		}
		if (isset($uriMap['query'])) {
			$uri->query = (string) Query::create($uriMap['query']);
		}
		if (isset($uriMap['fragment'])) {
			// no rawurldecode(), see property docs
			$uri->fragment = $uriMap['fragment'];
		}
		return $uri;
	}

	/**
	 * @return string
	 */
	public function __toString(): string {
		return $this->buildString();
	}

	public function isRelative(): bool {
		return $this->scheme === null && ($this->authority === null || $this->getAuthority()->isEmpty());
	}

	/**
	 * Converts host name to IDNA ASCII form.
	 * @return string
	 */
	public function toIdnaAsciiString(): string {
		return $this->buildString(false);
	}

	private function buildString(bool $idn = true): string {
		$str = '';

		$leadingPathDelimiter = null;

		if ($this->scheme !== null) {
			$str .= $this->scheme . self::SCHEME_SEPARATOR;
		}

		if ($this->authority !== null && !$this->getAuthority()->isEmpty()) {
			$str .= self::AUTHORITY_PREFIX . ($idn ? $this->authority : $this->getAuthority()->toIdnaAsciiString());
			$leadingPathDelimiter = $this->path !== null && !$this->getPath()->isEmpty();
		}

		if ($this->path !== null) {
			$str .= $this->getPath()->toRealString($leadingPathDelimiter);
		}

		if ($this->query !== null && !$this->getQuery()->isEmpty()) {
			$str .= self::QUERY_PREFIX . $this->query;
		}

		if ($this->fragment !== null) {
			// no rawurlencode(), see property docs.
			$str .= self::FRAGMENT_PREFIX . $this->fragment;
		}

		return $str;
	}

	/**
	 * @param $url
	 * @return bool
	 */
	private function storedAuthority(): ?Authority {
		return $this->authority === null ? null : $this->getAuthority();
	}

	private function storedPath(): ?Path {
		return $this->path === null ? null : $this->getPath();
	}

	private function storedQuery(): ?Query {
		return $this->query === null ? null : $this->getQuery();
	}

	public function equals(mixed $url): bool {
		return $url instanceof Url && (string) $this  === (string) $url;
	}

	public function toPsr(UriFactoryInterface $uriFactory): UriInterface {
		return $uriFactory->createUri((string) $this);
	}

	function jsonSerialize(): string {
		return $this->__toString();
	}
}
