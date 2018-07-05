<?php

namespace Mahalay\EzPhpSpec;

class Psr4Namespace
{
    /** @var string */
    private $namespace;

    /** @var string */
    private $sourcePath;

    public function __construct(string $namespace, string $sourcePath)
    {
        $this->namespace = $this->prepareNamespace($namespace);
        $this->sourcePath = $this->prepareSourcePath($sourcePath);
    }

    public static function fromPsr0(string $namespace, string $sourcePath)
    {
        return new static($namespace, preg_replace('/[\\\\\/]+/', '/', "{$sourcePath}/{$namespace}"));
    }

    public function prepareNamespace(string $namespace): string
    {
        return preg_replace('/[\\\]+$/i', '', trim($namespace));
    }

    public function getNamespace(): string
    {
        return $this->namespace;
    }

    public function prepareSourcePath(string $sourcePath): string
    {
        return preg_replace('/[\\/]+$/i', '', trim($sourcePath));
    }

    public function getSourcePath(): string
    {
        return $this->sourcePath;
    }

    public function toSuiteConfig(string $specPrefix): array
    {
        $namespace = $this->getNamespace();
        $suiteKey = $this->resolveSuiteKey($namespace, $this->getSourcePath());
        $psr4ConfigElements = ['psr4_prefix' => $namespace, 'spec_prefix' => sprintf('%s\\%s', $specPrefix, $suiteKey)];
        $suiteConfig = array_merge(
            [
                'namespace' => $namespace,
                'spec_prefix' => $specPrefix,
                'src_path' => $this->getSourcePath()
            ],
            ($namespace) ? $psr4ConfigElements : []
        );

        return [$suiteKey => $suiteConfig];
    }

    private function resolveSuiteKey(string $namespace, string $sourcePath): string
    {
        return sprintf(
            '%s_%s',
            preg_replace('/[\\\\s]+/i', '_', strtolower($namespace)),
            hash('crc32', $sourcePath)
        );
    }

    public function matchesNamespaces(string ...$namespacesToMatch): bool
    {
        return in_array(
            $this->getNamespace(),
            array_map([$this, 'prepareNamespace'], $namespacesToMatch)
        );
    }
}
