<?php

namespace Mahalay\EzPhpSpec\Locator;

use PhpSpec\Locator\ResourceManager as BaseResourceManager;

class ResourceManager implements BaseResourceManager
{
    /** @var BaseResourceManager */
    private $mainResourceManager;

    public function __construct(BaseResourceManager $resourceManager)
    {
        $this->mainResourceManager = $resourceManager;
    }

    /**
     * @inheritDoc
     */
    public function locateResources(string $query)
    {
        return $this->mainResourceManager->locateResources($query);
    }

    /**
     * @inheritDoc
     */
    public function createResource(string $classname): \PhpSpec\Locator\Resource
    {
        // TODO: Implement createResource() method.
    }
}
