Feature: Developer generates a spec
  As a Developer
  I want to automate creating specs
  In order to avoid repetitive tasks and interruptions in development flow

  Background: Autoload configuration from composer.json
    Given the "composer.json" file contains:
      """
      {
        "autoload": {
          "psr-4": {
            "\\": "alt-src/",
            "Acme\\": "acme-src/"
          }
        }
      }

      """
    And the "phpspec.yaml" file contains:
      """
      extensions:
        Mahalay\EzPhpSpec\Extension: []

      """

  Scenario: Generating a spec for a class with undefined namespace
    When I start describing the "CodeGeneration/SpecExample1/Markdown" class
    Then a new spec should be generated in the "spec/CodeGeneration/SpecExample1/MarkdownSpec.php":
      """
      <?php

      namespace spec\CodeGeneration\SpecExample1;

      use CodeGeneration\SpecExample1\Markdown;
      use PhpSpec\ObjectBehavior;
      use Prophecy\Argument;

      class MarkdownSpec extends ObjectBehavior
      {
          function it_is_initializable()
          {
              $this->shouldHaveType(Markdown::class);
          }
      }

      """

  Scenario: Generating a spec for a class matching a defined namespace
    When I start describing the "Acme/SpecExample1/Markdown" class
    Then a new spec should be generated in the "spec/acme_9ec9204a/Acme/SpecExample1/MarkdownSpec.php":
      """
      <?php

      namespace spec\acme_9ec9204a\Acme\SpecExample1;

      use Acme\SpecExample1\Markdown;
      use PhpSpec\ObjectBehavior;
      use Prophecy\Argument;

      class MarkdownSpec extends ObjectBehavior
      {
          function it_is_initializable()
          {
              $this->shouldHaveType(Markdown::class);
          }
      }

      """
