<?php

namespace spec\ezphpspec\Mahalay\EzPhpSpec\Exception;

use Exception as BaseException;
use Mahalay\EzPhpSpec\Exception\Exception;
use PhpSpec\ObjectBehavior;

/**
 * @mixin Exception
 */
class ExceptionSpec extends ObjectBehavior
{
    function it_is_derivative_of_Exception()
    {
        $this->shouldHaveType(BaseException::class);
    }
}
