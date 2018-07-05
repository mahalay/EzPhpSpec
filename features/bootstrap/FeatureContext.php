<?php

use Behat\Behat\Context\Context;
use Behat\Behat\Tester\Exception\PendingException;
use Behat\Gherkin\Node\PyStringNode;
use PhpSpec\Console\Application;
use PhpSpec\Loader\StreamWrapper;
use Symfony\Component\Console\Tester\ApplicationTester;
use Symfony\Component\Filesystem\Exception\IOException;
use Symfony\Component\Filesystem\Filesystem;

/**
 * Defines application features from the specific context.
 */
class FeatureContext implements Context
{
    /** @var Filesystem */
    private $filesystem;

    /** @var string */
    private $workingDirectory = '';

    private $phpSpecBin = '';

    /** @var ApplicationTester */
    private $tester;

    /** @var Application */
    private $application;

    public function __construct()
    {
        $this->filesystem = new Filesystem();
        $this->phpSpecBin = realpath('./vendor/phpspec/phpspec/bin/phpspec');
    }

    /**
     * @beforeScenario
     */
    public function setupScenario()
    {
        $this->initializeWorkingDirectory();
        $this->initializeApplicationTester();
    }

    private function setFixedTerminalDimensions()
    {
        putenv('COLUMNS=130');
        putenv('LINES=30');
    }

    /**
     * @afterScenario
     */
    public function tearDownScenario()
    {
        try {
            $this->filesystem->remove($this->workingDirectory);
        } catch (IOException $e) {
            //ignoring exception
        }
    }

    /**
     * @Given the :filename file contains:
     */
    public function theFileContains($filename, PyStringNode $contents)
    {
        $this->filesystem->dumpFile($filename, (string)$contents);
    }

    /**
     * @Given I have started describing the :class class
     * @Given I start describing the :class class
     *
     * @throws Exception
     */
    public function iDescribeTheClass($class)
    {
        $arguments = array(
            'command' => 'describe',
            'class' => $class
        );

        if ($this->tester->run($arguments, array('interactive' => false)) !== 0) {
            throw new \Exception('Test runner exited with an error');
        }
    }

    /**
     * @Then the class in :file should contain:
     * @Then a new class/spec should be generated in the :file:
     *
     * @throws Exception
     */
    public function theFileShouldContain($file, PyStringNode $contents)
    {
        var_dump(shell_exec('ls -lha spec/'));

        if (!file_exists($file)) {
            throw new \Exception(sprintf(
                "File did not exist at path '%s'",
                $file
            ));
        }


        $expectedContents = (string)$contents;
        if ($expectedContents != file_get_contents($file)) {
            throw new \Exception(sprintf(
                "File at '%s' did not contain expected contents.\nExpected: '%s'\nActual: '%s'",
                $file,
                $expectedContents,
                file_get_contents($file)
            ));
        }
    }

    /**
     * @Given I am cool
     */
    public function iAmCool()
    {
        throw new PendingException();
    }

    private function initializeWorkingDirectory(): void
    {
        $this->workingDirectory = tempnam(sys_get_temp_dir(), 'ezphpspec-behat');
        $this->filesystem->remove($this->workingDirectory);
        $this->filesystem->mkdir($this->workingDirectory);
        chdir($this->workingDirectory);
    }

    private function initializeApplicationTester(): void
    {
        StreamWrapper::register();

        $this->application = new Application('2.1-dev');
        $this->application->setAutoExit(false);
        $this->tester = new ApplicationTester($this->application);
        $this->setFixedTerminalDimensions();
    }
}
