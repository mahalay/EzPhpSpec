<?php

namespace spec\ezphpspec\Mahalay\EzPhpSpec\Locator;

use Mahalay\EzPhpSpec\Locator\ResourceManager;
use PhpSpec\Locator\ResourceManager as BaseResourceManager;
use PhpSpec\ObjectBehavior;

/**
 * @mixin ResourceManager
 */
class ResourceManagerSpec extends ObjectBehavior
{
    /** @var ResourceManager */
    private $phpspecResourceManager;

    function let(\PhpSpec\Locator\ResourceManager $resourceManager)
    {
        $this->beConstructedWith($this->phpspecResourceManager = $resourceManager);
    }

    function it_is_a_ResourceManager()
    {
        $this->shouldHaveType(BaseResourceManager::class);
    }
}
