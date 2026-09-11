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

class Url implements \JsonSerializable, Stringable {
	const SCHEME_SEPARATOR = ':';
	const AUTHORITY_PREFIX = '//';
	const PATH_PREFIX = Path::DELIMITER;
	const QUERY_PREFIX = '?';
	const FRAGMENT_PREFIX = '#';

	private readonly ?string $scheme;
	private readonly ?Authority $authority;
	private readonly ?Path $path;
	private readonly ?Query $query;

	/**
	 * According to RFC 2396, RFC 3986, and RFC 7320, the format of fragment identifiers depends on the media type.
	 * Therefore, this class leaves the fragment as it is and does not perform any encoding
	 *
	 * @var string|null
	 */
	private readonly ?string $fragment;

	public function __construct(?string $scheme = null, ?Authority $authority = null, ?Path $path = null,
			?Query $query = null, ?string $fragment = null) {
		$this->scheme = ArgUtils::stringOrNull($scheme);
		$this->authority = $authority;
		$this->path = $path;
		$this->query = $query;
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
		return $this->authority;
	}
	/**
	 * @return Path
	 */
	public function getPath(): Path {
		if ($this->path === null) {
			return new Path(array());
		}
		return $this->path;
	}
	/**
	 * @return Query
	 */
	public function getQuery(): Query {
		if ($this->query === null) {
			return new Query(array());
		}
		return $this->query;
	}
	/**
	 * @return string|null
	 */
	public function getFragment(): ?string {
		return $this->fragment;
	}
	/**
	 * @param string|null $scheme
	 * @return Url
	 */
	public function chScheme(?string $scheme = null): Url {
		if ($scheme === $this->scheme) return $this;
		return new Url($scheme, $this->authority, $this->path, $this->query, $this->fragment);
	}
	/**
	 * @param mixed $authority
	 * @return Url
	 */
	public function chAuthority(mixed $authority): Url {
		$authority = Authority::create($authority);
		if ($this->authority !== null && (string) $authority === (string) $this->authority) {
			return $this;
		}
		return new Url($this->scheme, $authority, $this->path, $this->query, $this->fragment);
	}
	/**
	 * @param string|null $userInfo
	 * @return Url
	 */
	public function chUserInfo(?string $userInfo): Url {
		$authority = $this->getAuthority();
		$userInfoParts = $userInfo === null ? array(null, null) : explode(':', $userInfo, 2);
		$user = $userInfoParts[0];
		$password = $userInfoParts[1] ?? null;
		if ($authority->getUser() === $user && $authority->getPassword() === $password) {
			return $this;
		}

		return new Url($this->scheme,
				new Authority($authority->getHost(), $authority->getPort(), $user, $password),
				$this->path, $this->query, $this->fragment);
	}
	/**
	 * @param string|null $host
	 * @return Url
	 */
	public function chHost(?string $host = null): Url {
		if ($this->getAuthority()->getHost() === $host) return $this;
		return new Url($this->scheme, $this->getAuthority()->chHost($host), $this->path,
				$this->query, $this->fragment);
	}
	/**
	 * @param int|null $port
	 * @return Url
	 */
	public function chPort(?int $port = null): Url {
		if ($this->getAuthority()->getPort() === $port) return $this;
		return new Url($this->scheme, $this->getAuthority()->chPort($port), $this->path,
				$this->query, $this->fragment);
	}
	/**
	 * @param string|Path|null $path
	 * @return Url
	 */
	public function chPath(string|Path|null $path = null): Url {
		$path = Path::create($path);
		if ($this->path !== null && (string) $path === (string) $this->path) return $this;
		return new Url($this->scheme, $this->authority, $path, $this->query, $this->fragment);
	}
	
	/**
	 * @param bool $endingDelimitter
	 * @return Url
	 */
	public function chPathEndingDelimiter(bool $endingDelimitter): Url {
		return $this->chPath($this->getPath()->chEndingDelimiter($endingDelimitter));
	}
	
	/**
	 * @param mixed $query
	 * @return Url
	 */
	public function chQuery(mixed $query): Url {
		$query = Query::create($query);
		if ($this->query !== null && (string) $query === (string) $this->query) return $this;
		return new Url($this->scheme, $this->authority, $this->path, $query, $this->fragment);
	}
	/**
	 * @param string|null $fragment
	 * @return Url
	 */
	public function chFragment(?string $fragment): Url {
		if ($fragment === $this->fragment) return $this;
		return new Url($this->scheme, $this->authority, $this->path, $this->query, $fragment);
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
	 * @param mixed $pathExt
	 * @param mixed $queryExt
	 * @param string|null $fragment
	 * @return Url
	 */
	public function extR(mixed $pathExt = null, mixed $queryExt = null, ?string $fragment = null): Url {
		if ($pathExt === null && $queryExt === null && $fragment === null) return $this;

		return new Url($this->scheme, $this->authority, $this->getPath()->ext($pathExt),
				$this->getQuery()->ext($queryExt),
				($fragment === null ? $this->fragment : $fragment));
	}

	/**
	 * @param mixed ...$pathPartExts
	 * @return Url
	 */
	public function pathExt(mixed ...$pathPartExts): Url {
		return new Url($this->scheme, $this->authority, $this->getPath()->ext(...$pathPartExts),
				$this->query, $this->fragment);
	}

	/**
	 * @param mixed ...$pathExts
	 * @return Url
	 */
	public function pathExtEnc(mixed ...$pathExts): Url {
		return new Url($this->scheme, $this->authority, $this->getPath()->extEnc(...$pathExts),
				$this->query, $this->fragment);
	}

	/**
	 * @param mixed $query
	 * @return Url
	 */
	public function queryExt(mixed $query): Url {
		return new Url($this->scheme, $this->authority, $this->getPath(), $this->getQuery()->ext($query),
				$this->fragment);
	}

	/**
	 * @param int $num
	 * @return Url
	 */
	public function reducedPath(int $num = 1): Url {
		return new Url($this->scheme, $this->authority, $this->getPath()->reduced($num),
				$this->query, $this->fragment);
	}
	/**
	 * @param int $start
	 * @param int|null $num
	 * @return Url
	 */
	public function subPath(int $start, ?int $num = null): Url {
		return new Url($this->scheme, $this->authority, $this->getPath()->sub($start, $num),
				$this->query, $this->fragment);
	}
	/**
	 * @return Url
	 */
	public function toRelativeUrl(): Url {
		return new Url(null, null, $this->path, $this->query, $this->fragment);
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
	 * @return Url
	 *@throws \InvalidArgumentException
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

		return new Url($uriMap['scheme'] ?? null,
				isset($uriMap['host']) || isset($uriMap['user'])
						? new Authority($uriMap['host'] ?? null, $uriMap['port'] ?? null,
								isset($uriMap['user']) ? rawurldecode($uriMap['user']) : null,
								isset($uriMap['pass']) ? rawurldecode($uriMap['pass']) : null)
						: null,
				isset($uriMap['path']) ? Path::create($uriMap['path'], $lenient) : null,
				isset($uriMap['query']) ? Query::create($uriMap['query']) : null,
				$uriMap['fragment'] ?? null);
	}

	/**
	 * @return string
	 */
	public function __toString(): string {
		return $this->buildString();
	}

	public function isRelative(): bool {
		return $this->scheme === null && ($this->authority === null || $this->authority->isEmpty());
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

		if ($this->authority !== null && !$this->authority->isEmpty()) {
			$str .= self::AUTHORITY_PREFIX . ($idn ? (string) $this->authority : $this->authority->toIdnaAsciiString());
			$leadingPathDelimiter = $this->path !== null && !$this->path->isEmpty();
		}

		if ($this->path !== null) {
			$str .= $this->path->toRealString($leadingPathDelimiter);
		}

		if ($this->query !== null && !$this->query->isEmpty()) {
			$str .= self::QUERY_PREFIX . $this->query;
		}

		if ($this->fragment !== null) {
			// no rawurlencode(), see property docs.
			$str .= self::FRAGMENT_PREFIX . $this->fragment;
		}

		return $str;
	}

	/**
	 * @param mixed $url
	 * @return bool
	 */
	public function equals(mixed $url): bool {
		return $url instanceof Url && (string) $this === (string) $url;
	}

	public function toPsr(UriFactoryInterface $uriFactory): UriInterface {
		return $uriFactory->createUri((string) $this);
	}

	public function jsonSerialize(): string {
		return $this->__toString();
	}
}
