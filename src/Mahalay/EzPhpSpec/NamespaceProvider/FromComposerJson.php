<?php

namespace Mahalay\EzPhpSpec\NamespaceProvider;

use Mahalay\EzPhpSpec\Psr4Namespace;
use PhpSpec\Util\Filesystem;

class FromComposerJson
{
    /** @var Filesystem */
    private $filesystem;

    /** @var string */
    private $composerJsonPath;

    public function __construct(Filesystem $filesystem, string $composerJsonPath)
    {
        $this->filesystem = $filesystem;
        $this->composerJsonPath = $composerJsonPath;
    }

    /**
     * @param  string[] $namespacesToExclude
     *
     * @return Psr4Namespace[]
     */
    public function getNamespaces(array $namespacesToExclude = array()): array
    {
        return array_values(array_filter(
            $this->getUnfilteredNamespaces(),
            function (Psr4Namespace $namespace) use ($namespacesToExclude) {
                return !$namespace->matchesNamespaces(...$namespacesToExclude);
            }
        ));
    }

    /**
     * @return Psr4Namespace[]
     */
    private function getUnfilteredNamespaces(): array
    {
        $decodedJson = (array)json_decode($x = $this->filesystem->getFileContents($this->composerJsonPath), true);
        $namespaces = array_merge($this->parsePsr4Namespaces($decodedJson), $this->parsePsr0Namespaces($decodedJson));

        return array_reduce($namespaces, [$this, 'removeDuplicateNamespace'], []);
    }

    /**
     * @return Psr4Namespace[]
     */
    private function parsePsr4Namespaces(array $decodedJson): array
    {
        $psr4Autoload = $this->extractAutoloadElements('psr-4', $decodedJson);

        return array_reduce(
            array_keys($psr4Autoload),
            function (array $carry, string $namespace) use ($psr4Autoload) {
                return array_merge(
                    $carry,
                    $this->createPsr4NamespacesBySourcePaths($namespace, (array)$psr4Autoload[$namespace])
                );
            },
            []
        );
    }

    /**
     * @return Psr4Namespace[]
     */
    private function parsePsr0Namespaces(array $decodedJson): array
    {
        $psr0Autoload = (isset($decodedJson['autoload']) && isset($decodedJson['autoload']['psr-0']))
            ? (array)$decodedJson['autoload']['psr-0']
            : [];

        return array_reduce(
            array_keys($psr0Autoload),
            function (array $carry, string $namespace) use ($psr0Autoload) {
                $namespaces = $this->createPsr0NamespacesBySourcePaths($namespace, (array)$psr0Autoload[$namespace]);
                return array_merge($carry, $namespaces);
            },
            []
        );
    }

    private function extractAutoloadElements(string $standard, array $decodedJson): array
    {
        return (isset($decodedJson['autoload']) && isset($decodedJson['autoload'][$standard]))
            ? (array)$decodedJson['autoload'][$standard]
            : []
            ;
    }

    private function createPsr4NamespacesBySourcePaths(string $namespace, array $sourcePaths)
    {
        if ($sourcePath = array_pop($sourcePaths)) {
            return array_merge(
                $this->createPsr4NamespacesBySourcePaths($namespace, $sourcePaths),
                [new Psr4Namespace($namespace, $sourcePath)]
            );
        }

        return [];
    }

    private function createPsr0NamespacesBySourcePaths(string $namespace, array $sourcePaths)
    {
        if ($sourcePath = array_pop($sourcePaths)) {
            return array_merge(
                $this->createPsr0NamespacesBySourcePaths($namespace, $sourcePaths),
                [Psr4Namespace::fromPsr0($namespace, $sourcePath)]
            );
        }

        return [];
    }

    private function removeDuplicateNamespace(array $carry, Psr4Namespace $namespace)
    {
        $hashKey = hash('crc32', "{$namespace->getNamespace()}/{$namespace->getSourcePath()}");
        if (!isset($carry[$hashKey])) {
            $carry[$hashKey] = $namespace;
        }

        return $carry;
    }
}
