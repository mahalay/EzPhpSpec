<?php

namespace spec\ezphpspec\Mahalay\EzPhpSpec;

use Mahalay\EzPhpSpec\Extension;
use PhpSpec\ObjectBehavior;
use PhpSpec\ServiceContainer;
use PhpSpec\Wrapper\Collaborator;

/**
 * @mixin Extension
 */
class ExtensionSpec extends ObjectBehavior
{
    /** @var string */
    private $rootDir;

    function let()
    {
        $this->rootDir = sys_get_temp_dir() . '/ezphpspec-' . hash('crc32', uniqid());
        shell_exec('mkdir -p ' . escapeshellarg($this->rootDir));
    }

    function letGo()
    {
        shell_exec('rm -fr ' . escapeshellarg($this->rootDir));
    }

    function it_should_initialize_phpspec_using_autoload_in_composer_json_file(ServiceContainer $container)
    {
        $this->prepareTheComposerDotJsonFile(<<<'JSON'
{
  "autoload": {
    "psr-4": {
      "Acme\\App\\": "src/"
    },
    "psr-0": {
      "Acme\\Lib\\": "lib/",
      "": "sauce-path/"
    }
  }
}

JSON
        );
        $this->specifyThatParametersWillBeRetrieved($container);
        $this->specifyThatParametersWillBeSet($container);

        $this->load($container, array());
    }

    function it_should_initialize_phpspec_with_default_suite_when_there_is_no_usable_config(ServiceContainer $container)
    {
        $_defaultSuite = [
            '_' . hash('crc32', 'src') => [
                'namespace' => '',
                'src_path' => 'src',
                'spec_prefix' => 'spec',
            ]
        ];
        $container->getParam('composer_suite_detection', false)->willReturn(['root_directory' => $this->rootDir]);
        $container->getParam('suites', [])->willReturn([]);
        $container->setParam('composer_suite_detection', false)->shouldBeCalledTimes(1);
        $container->setParam('suites', $_defaultSuite)->shouldBeCalledTimes(1);

        $this->load($container, array());
    }


    private function prepareTheComposerDotJsonFile(string $contents)
    {
        file_put_contents(sprintf("%s/%s", $this->rootDir, 'composer.json'), $contents);
    }

    /**
     * @param ServiceContainer $container
     */
    private function specifyThatParametersWillBeRetrieved(Collaborator $container)
    {
        $container
            ->getParam('composer_suite_detection', false)
            ->shouldBeCalledTimes(1)
            ->willReturn($this->theSuiteDetectionConfig())
        ;

        $container
            ->getParam('suites', array())
            ->shouldBeCalledTimes(1)
            ->willReturn($this->theSuiteConfigThatOverridesComposerAutoload())
        ;
    }

    private function theSuiteDetectionConfig(): array
    {
        return [
            'root_directory' => $this->rootDir,
            'spec_prefix' => 'cool'
        ];
    }

    private function theSuiteConfigThatOverridesComposerAutoload(): array
    {
        return [
            'acme_lib' => [
                'namespace' => 'Acme\\Lib',
            ]
        ];
    }

    /**
     * @param ServiceContainer $container
     */
    private function specifyThatParametersWillBeSet(Collaborator $container)
    {
        $container
            ->setParam('composer_suite_detection', false)
            ->shouldBeCalledTimes(1)
        ;

        $container
            ->setParam(
                'suites',
                array_merge(
                    $this->theSuiteConfigThatOverridesComposerAutoload(),
                    $this->theSuiteDerivedFromComposerDotJson()
                )
            )
            ->shouldBeCalledTimes(1)
        ;
    }

    private function theSuiteDerivedFromComposerDotJson(): array
    {
        return [
            'acme_app_1eb1e66a' => [
                'namespace'=> 'Acme\App',
                'psr4_prefix'=> 'Acme\App',
                'spec_prefix'=> 'cool\acme_app_1eb1e66a',
                'src_path' => 'src',
            ],
            '_' . hash('crc32', 'sauce-path') => [
                'namespace' => '',
                'spec_prefix' => 'cool',
                'src_path' => 'sauce-path',
            ]
        ];
    }
}
