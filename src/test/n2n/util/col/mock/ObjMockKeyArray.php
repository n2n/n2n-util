<?php

namespace n2n\util\col\mock;

use n2n\util\col\TypedArray;
use n2n\util\type\TypeConstraint;
use n2n\util\type\TypeConstraints;
use n2n\util\col\attribute\KeyType;
use n2n\util\col\attribute\ValueType;

/**
 * @extends TypedArray<ObjMock, string>
 */
#[KeyType(ObjMock::class)]
#[ValueType('string', convertable: true)]
class ObjMockKeyArray extends TypedArray {

}