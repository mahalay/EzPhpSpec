<?php

namespace spec\ezphpspec\Mahalay\EzPhpSpec\NamespaceProvider;

use Mahalay\EzPhpSpec\Psr4Namespace;
use Mahalay\EzPhpSpec\NamespaceProvider\FromComposerJson;
use PhpSpec\ObjectBehavior;
use PhpSpec\Util\Filesystem;

/**
 * @mixin FromComposerJson
 */
class FromComposerJsonSpec extends ObjectBehavior
{
    /** @var Filesystem */
    private $filesystem;

    function let(Filesystem $fs)
    {
        $this->beConstructedWith($this->filesystem, '');
        $this->filesystem = $fs;
    }

    function it_should_return_an_empty_array_if_autoload_is_missing()
    {
        $jsonPath = 'path-to-composer.json';
        $this->beConstructedWith($this->filesystem, $jsonPath);
        $this->emulateComposerJsonFileBeingReadReturningAJsonStream($this->filesystem, $jsonPath, '');

        $this->getNamespaces()->shouldReturn([]);
    }

    function it_should_return_namespaces_matching_the_composer_PSR4_autoload()
    {
        $composerJson = <<<'JSON'
{
  "autoload": {
    "psr-4": {
      "Foo\\Bar\\": "src/",
      "CoolSpec\\": "src/CoolSpec"
    }
  }
}
JSON;
        $this->beConstructedWith($this->filesystem, $jsonPath = 'path-to-composer.json');
        $this->emulateComposerJsonFileBeingReadReturningAJsonStream($this->filesystem, $jsonPath, $composerJson);

        $this->getNamespaces()->shouldBeLike([
            new Psr4Namespace('Foo\\Bar\\', 'src/'),
            new Psr4Namespace('CoolSpec\\', 'src/CoolSpec'),
        ]);
    }

    function it_should_return_namespaces_matching_the_source_paths_of_PSR4_namespace()
    {
        $composerJson = <<<'JSON'
{
  "autoload": {
    "psr-4": {
      "Foo\\Bar\\": ["src/", "lib/"]
    }
  }
}
JSON;
        $this->beConstructedWith($this->filesystem, $jsonPath = 'path-to-composer.json');
        $this->emulateComposerJsonFileBeingReadReturningAJsonStream($this->filesystem, $jsonPath, $composerJson);

        $this->getNamespaces()->shouldBeLike([
            new Psr4Namespace('Foo\\Bar\\', 'src/'),
            new Psr4Namespace('Foo\\Bar\\', 'lib/')
        ]);
    }

    function it_should_return_namespaces_matching_the_composer_PSR0_autoload()
    {
        $composerJson = <<<'JSON'
{
  "autoload": {
    "psr-0": {
      "Coolness\\Hotness\\": "lib/"
    }
  }
}
JSON;
        $this->beConstructedWith($this->filesystem, $jsonPath = 'path-to-composer.json');
        $this->emulateComposerJsonFileBeingReadReturningAJsonStream($this->filesystem, $jsonPath, $composerJson);

        $this->getNamespaces()->shouldBeLike([
            Psr4Namespace::fromPsr0('Coolness\\Hotness\\', 'lib/'),
        ]);
    }

    function it_should_return_namespaces_matching_the_source_paths_of_PSR0_namespace()
    {
        $composerJson = <<<'JSON'
{
  "autoload": {
    "psr-0": {
      "Coolness\\Hotness\\": ["lib/", "src/"]
    }
  }
}
JSON;
        $this->beConstructedWith($this->filesystem, $jsonPath = 'path-to-composer.json');
        $this->emulateComposerJsonFileBeingReadReturningAJsonStream($this->filesystem, $jsonPath, $composerJson);

        $this->getNamespaces()->shouldBeLike([
            Psr4Namespace::fromPsr0('Coolness\\Hotness\\', 'lib/'),
            Psr4Namespace::fromPsr0('Coolness\\Hotness\\', 'src/'),
        ]);
    }

    function it_should_return_namespaces_matching_the_composer_PSR0_and_PSR4_autoload()
    {
        $composerJson = <<<'JSON'
{
  "autoload": {
    "psr-4": {
      "Foo\\Bar\\": "src/"
    },
    "psr-0": {
      "Coolness\\Hotness\\": "lib/"
    }
  }
}
JSON;
        $this->beConstructedWith($this->filesystem, $jsonPath = 'path-to-composer.json');
        $this->emulateComposerJsonFileBeingReadReturningAJsonStream($this->filesystem, $jsonPath, $composerJson);

        $this->getNamespaces()->shouldBeLike([
            new Psr4Namespace('Foo\\Bar', 'src'),
            Psr4Namespace::fromPsr0('Coolness\\Hotness\\', 'lib/')
        ]);
    }

    function it_should_return_unique_namespaces_for_PSR0_and_PSR4()
    {
        $composerJson = <<<'JSON'
{
  "autoload": {
    "psr-4": {
      "Foo\\Bar\\": ["src/", "src/"],
      "Coolness\\Hotness\\": "lib/Coolness/Hotness/"
    },
    "psr-0": {
      "Coolness\\Hotness\\": "lib/"
    }
  }
}
JSON;

        $this->beConstructedWith($this->filesystem, $jsonPath = 'path-to-composer.json');
        $this->emulateComposerJsonFileBeingReadReturningAJsonStream($this->filesystem, $jsonPath, $composerJson);

        $this->getNamespaces()->shouldBeLike([
            new Psr4Namespace('Foo\\Bar\\', 'src/'),
            new Psr4Namespace('Coolness\\Hotness\\', 'lib/Coolness/Hotness/'),
        ]);
    }

    function it_should_return_namespaces_excluding_given_namespaces()
    {
        $composerJson = <<<'JSON'
{
  "autoload": {
    "psr-4": {
      "Foo\\Bar\\": "src/"
    },
    "psr-0": {
      "Coolness\\Hotness\\": "lib/"
    }
  }
}
JSON;
        $this->beConstructedWith($this->filesystem, $jsonPath = 'path-to-composer.json');
        $this->emulateComposerJsonFileBeingReadReturningAJsonStream($this->filesystem, $jsonPath, $composerJson);

        $this->getNamespaces(array('Foo\\Bar'))->shouldBeLike([
            Psr4Namespace::fromPsr0('Coolness\\Hotness\\', 'lib/')
        ]);
    }

    private function emulateComposerJsonFileBeingReadReturningAJsonStream(
        Filesystem $filesystem,
        string $composerJsonPath,
        string $returnedJsonStream
    )
    {
        $filesystem->getFileContents($composerJsonPath)->shouldBeCalledTimes(1)->willReturn($returnedJsonStream);
    }
}
