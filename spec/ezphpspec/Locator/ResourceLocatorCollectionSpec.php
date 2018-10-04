<?php

namespace spec\ezphpspec\Mahalay\EzPhpSpec\Locator;

use Mahalay\EzPhpSpec\Locator\ResourceLocatorCollection;
use PhpSpec\ObjectBehavior;

class ResourceLocatorCollectionSpec extends ObjectBehavior
{
    function it_is_initializable()
    {
        $this->shouldHaveType(ResourceLocatorCollection::class);
    }
}
