<?php

namespace spec\ezphpspec\Mahalay\EzPhpSpec\Exception;

use Mahalay\EzPhpSpec\Exception\AmbiguousLocatorMatch;
use Mahalay\EzPhpSpec\Exception\Exception;
use PhpSpec\ObjectBehavior;

class AmbiguousLocatorMatchSpec extends ObjectBehavior
{
    function it_is_derivative_of_EzPhpSpec_Exception()
    {
        $this->shouldHaveType(AmbiguousLocatorMatch::class);
        $this->shouldHaveType(Exception::class);
    }
}
