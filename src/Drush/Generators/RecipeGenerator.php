<?php

declare(strict_types=1);

namespace kevinquillen\Drush\Generators;

use DrupalCodeGenerator\Command\DrupalGenerator;
use Symfony\Component\Console\Question\Question;

/**
 * Implements Recipe generator command.
 */
final class RecipeGenerator extends DrupalGenerator {

  public const EXTENSION_TYPE_RECIPE = 0x04;

  /**
   * {@inheritdoc}
   */
  protected ?int $extensionType = self::EXTENSION_TYPE_RECIPE;

  /**
   * The Drush generator command name.
   *
   * @var string
   */
  protected string $name = 'recipe';

  /**
   * The Drush generator command description.
   *
   * @var string
   */
  protected string $description = 'Generates a Recipe for adding new functionality to Drupal.';

  /**
   * The path to the templates for the generator.
   *
   * @var string
   */
  protected string $templatePath = __DIR__ . '/../../../templates';

  /**
   * {@inheritdoc}
   */
  protected function getExtensionList(): array {
    return [];
  }

  /**
   * {@inheritdoc}
   */
  protected function generate(array &$vars): void {
    $vars['recipe_name'] = $this->ask('What is the name of this recipe?', 'My Custom Recipe', '::validateRequired');
    $vars['recipe_directory'] = $this->ask('In what directory should this recipe be saved under /recipes (ex. "my-recipe")?', 'my-custom-recipe', '::validateRequired');
    $vars['recipe_type'] = $this->ask('What type of recipe is this (Site, Content Type, Workflow, etc)?', NULL, '::validateRequired');
    $vars['recipe_description'] = $this->ask('What does this recipe do?', NULL, '::validateRequired');
    $vars['composer'] = $this->collectComposerInfo($vars);
    $vars['modules'] = $this->collectModules($vars);
    $vars['config'] = $this->collectConfig($vars);

    if (!empty($vars['composer'])) {
      $this->addFile('composer.json', 'composer/composer.json');
    }

    $this->addFile('recipe.yml', 'recipe/recipe.yml');
  }

  /**
   * Returns destination for generated recipes.
   */
  public function getDestination(array $vars): ?string {
    return $this->drupalContext->getDrupalRoot() . '/recipes/custom/' . $vars['recipe_directory'];
  }

  /**
   * Collects Composer related information from the user.
   *
   * @param array $vars
   *   The input vars.
   * @param bool $default
   *   The users choice.
   *
   * @return array
   */
  protected function collectComposerInfo(array &$vars, bool $default = TRUE): array {
    $vars['composer'] = [];

    if (!$this->confirm('Would you like to add a composer.json file for this recipe? This will let you declare dependencies.', $default)) {
      return $vars['composer'];
    }

    $question = new Question('Enter the vendor name.', 'drupal');
    $vendor_name = $this->io()->askQuestion($question);

    $question = new Question('Enter the package name.', 'my-custom-recipe');
    $package_name = $this->io()->askQuestion($question);

    $question = new Question('What is your name? This will be set as the author.', 'Developer');
    $author_name = $this->io()->askQuestion($question);

    $vars['composer']['vendor_name'] = $vendor_name;
    $vars['composer']['package_name'] = $package_name;
    $vars['composer']['author_name'] = $author_name;

    while (TRUE) {
      $question = new Question('Enter the name of the dependency to add (ex. drupal/pathauto).');
      $dependency = $this->io()->askQuestion($question);

      if (!$dependency) {
        break;
      }

      $question = new Question('Enter the version of this dependency to require (ex. ^1.0)');
      $version = $this->io()->askQuestion($question);

      $vars['composer']['dependencies'][] = [
        'name' => $dependency,
        'version' => $version,
      ];
    }

    return $vars['composer'];
  }

  /**
   * Collects module related information from the user.
   *
   * @param array $vars
   *   The input vars.
   * @param bool $default
   *   The users choice.
   *
   * @return array
   */
  protected function collectModules(array &$vars, bool $default = TRUE): array {
    $vars['modules'] = [];

    if (!$this->confirm('Would you like to add modules to install for this recipe?', $default)) {
      return $vars['modules'];
    }

    while (TRUE) {
      $question = new Question('Enter the name of the module to add (ex. node).');
      $module = $this->io()->askQuestion($question);

      if (!$module) {
        break;
      }

      $vars['modules'][] = $module;
    }

    return $vars['modules'];
  }

  /**
   * Collects configuration related information from the user.
   *
   * @param array $vars
   *   The input vars.
   * @param bool $default
   *   The users choice.
   *
   * @return array
   */
  protected function collectConfig(array &$vars, bool $default = TRUE): array {
    $vars['config'] = [];

    if ($this->confirm('Would you like to run specific config imports for this recipe?', $default)) {
      while (TRUE) {
        $config = [];
        $question = new Question('What module do you want to import config for (ex. node)?');
        $module = $this->io()->askQuestion($question);

        if (!$module) {
          break;
        }

        if (!$this->confirm("Do you want to import all config for $module (including optional config)?", $default)) {
          while (TRUE) {
            $question = new Question("Enter the config file you want to import for $module (without the .yml extension).");
            $filename = $this->io()->askQuestion($question);

            if (!$filename) {
              break;
            }

            $config[] = $filename;
          }
        }

        $vars['config']['import'][] = [
          'module_name' => $module,
          'config' => (empty($config)) ? '*' : $config,
        ];
      }
    }

    if ($this->confirm('Would you like to run config actions for this recipe?', $default)) {
      // ask for config actions
      // ask for action type
    }

    return $vars['config'];
  }

}
