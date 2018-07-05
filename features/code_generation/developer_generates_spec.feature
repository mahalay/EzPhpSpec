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
            "Acme\\" "acme-src/"
          }
        }
      }

      """

  Scenario: Generating a spec with namespace not defined in autoload matches empty namespace
    Given I am cool
