<?php

namespace n2n\util\col\mock;

use n2n\util\col\TypedArray;
use n2n\util\col\attribute\ValueType;

/**
 * @extends TypedArray<scalar, ObjMock>
 */
#[ValueType(ObjMock::class)]
class ObjMockArray extends TypedArray {

}