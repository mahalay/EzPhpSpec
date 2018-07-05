<?php

namespace Mahalay\EzPhpSpec;

use Mahalay\EzPhpSpec\NamespaceProvider\FromComposerJson;
use PhpSpec\Extension as BaseExtension;
use PhpSpec\ServiceContainer;
use PhpSpec\Util\Filesystem;

class Extension implements BaseExtension
{
    public function load(ServiceContainer $container, array $params)
    {
        $detectionConfig = $this->getSuiteDetectionConfig($container);
        $suitesFromPhpspecYaml = $container->getParam('suites', []);

        $namespacesFromComposer = $this->extractDefaultNamespacesOrFromComposerDotJson(
            $detectionConfig['root_directory'],
            array_map(
                array($this, 'extractNamespaceFromSuite'),
                array_values($suitesFromPhpspecYaml)
            )
        );

        $updatedSuites = array_reduce(
            $namespacesFromComposer,
            function (array $carry, Psr4Namespace $namespace) use ($detectionConfig) {
                return array_merge($carry, $namespace->toSuiteConfig($detectionConfig['spec_prefix']));
            },
            $suitesFromPhpspecYaml
        );

        $this->overrideContainerParameters($container, $updatedSuites);
    }

    private function extractNamespaceFromSuite(array $suite): string
    {
        return (isset($suite['namespace'])) ? $suite['namespace'] : '';
    }

    /**
     * @param ServiceContainer $container
     * @return array
     */
    private function getSuiteDetectionConfig(ServiceContainer $container): array
    {
        return array_merge(
            ['root_directory' => '.', 'spec_prefix' => 'spec'],
            (array)$container->getParam('composer_suite_detection', false)
        );
    }

    /**
     * @return Psr4Namespace[]
     */
    private function extractDefaultNamespacesOrFromComposerDotJson(
        string $rootDirectory,
        array $namespacesToExclude
    ): array {
        if (!is_readable($composerJsonPath = "{$rootDirectory}/composer.json")) {
            return [Psr4Namespace::fromPsr0('', 'src')];
        }

        $namespaceProvider = new FromComposerJson(new Filesystem(), $composerJsonPath);

        return $namespaceProvider->getNamespaces($namespacesToExclude);
    }

    private function overrideContainerParameters(ServiceContainer $container, array $updatedSuites): void
    {
        $container->setParam('composer_suite_detection', false);
        $container->setParam('suites', $updatedSuites);
    }
}
