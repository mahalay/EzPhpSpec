<?php

namespace spec\ezphpspec\Mahalay\EzPhpSpec;

use Mahalay\EzPhpSpec\Psr4Namespace;
use PhpSpec\ObjectBehavior;

/**
 * @mixin Psr4Namespace
 */
class Psr4NamespaceSpec extends ObjectBehavior
{
    function it_should_represent_a_namespace()
    {
        $this->beConstructedWith($namespace = 'Foo\\Bar\\', $srcPath = 'src/ ');

        $this->getNamespace()->shouldReturn('Foo\\Bar');
        $this->getSourcePath()->shouldReturn('src');
    }

    function it_should_convert_into_a_psr4_suite_config()
    {
        $this->beConstructedWith($namespace = 'Foo\\Bar', $srcPath = 'src');
        $key = 'foo_bar_' . hash('crc32', $srcPath);

        $this->toSuiteConfig('spec')->shouldReturn([
            $key => [
                'namespace' => $namespace,
                'psr4_prefix' => $namespace,
                'spec_prefix' => "spec\\{$key}",
                'src_path' => $srcPath
            ]
        ]);
    }

    function it_should_accept_psr0_namespace()
    {
        $this->beConstructedThrough('fromPsr0', [$namespace = 'Foo\\Bar\\', 'lib/']);

        $this->shouldBeLike(new Psr4Namespace($namespace, 'lib/Foo/Bar'));
    }

    function it_should_match_namespace_with_trailing_separator_to_one_without()
    {
        $this->beConstructedWith($namespace = 'Foo\\Bar', $srcPath = 'src');

        $this->shouldBeLike(new Psr4Namespace('Foo\\Bar\\', $srcPath));
    }

    function it_should_match_source_path_with_trailing_separator_to_one_without()
    {
        $this->beConstructedWith($namespace = 'Foo\\Bar', $srcPath = 'src/');

        $this->shouldBeLike(new Psr4Namespace($namespace, 'src'));
    }

    function it_should_return_true_with_namespace_that_match()
    {
        $this->beConstructedWith($namespace = 'Foo\\Bar', $srcPath = 'src/');

        $this->matchesNamespaces('Foo\\Bar\\' ,'This\\DoNot\\Match')->shouldReturn(true);
    }

    function it_should_return_false_with_namespace_that_do_not_match()
    {
        $this->beConstructedWith($namespace = 'Foo\\Bar', $srcPath = 'src/');

        $this->matchesNamespaces('Wow\\Nice\\')->shouldReturn(false);
    }
}
