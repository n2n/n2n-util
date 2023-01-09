<?php

namespace n2n\util\ex;

interface LogInfo {

	function hashCode(): ?string;

	function getLogMessage(): ?string;
}