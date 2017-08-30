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
function test(...$value) {
	if (n2n\core\N2N::isLiveStageOn()) return;
	$tab = '    ';
	$testClosure = function($testClosure, bool $applyPre, string $prepend, array $value) use ($tab) {
		$testArrayFunc = function(string $prepend, $name, $values) use ($testClosure, $tab) {
			echo $prepend . $name ;
			if (is_array($values) || (is_object($values) && is_a($values, 'Countable'))) {
				echo '(' . count($values) . ')';
			}
			
			echo ' {' . "\r\n";
			foreach ($values as $key => $value) {
				echo $prepend .  $tab . '[' . $key . ']=>' . "\r\n";
				$testClosure($testClosure, false, $prepend . $tab, array($value));
			}
			
			echo $prepend . '}';
		};
		
		foreach ($value as $v) {
			if ($applyPre) {
				echo "\r\n<pre>\r\n";
			}
			if (is_object($v)) {
				if (is_a($v, 'Traversable')) {
					$testArrayFunc($prepend, 'object(' . get_class($v) . ')',  $v);
				} else {
					echo $prepend . 'object(' . get_class($v) . ')';
					if (method_exists($v, '__toString')) {
						echo ": " . $v;
					} else if (method_exists($v, 'getId') && is_callable(array($v, 'getId'))) {
						$id = $v->getId();
						if (is_scalar($id)) {
							echo ": #" . $id;
						}
					}
					echo "\r\n";
				}
			} else {
				if (is_array($v)) {
					$testArrayFunc($prepend, 'array', $v);
					echo "\r\n";
				} else {
					echo $prepend;
					var_dump($v);
				}
			}
			if ($applyPre) {
				echo "</pre>\r\n";
			}
		}
	};
	
	$testClosure($testClosure, true, '', $value);
}
