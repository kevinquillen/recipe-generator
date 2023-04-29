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
  protected function generate(array &$vars): void {
    $this->collectDefault($vars);
    $vars['composer'] = $this->collectComposerInfo($vars);

    $this->addFile('composer.json', 'composer/composer.json')->skipIfExists();
  }

  /**
   * Returns destination for generated recipes.
   */
  public function getDestination(array $vars): ?string {
    $recipes_dir = \is_dir($this->drupalContext->getDrupalRoot() . '/recipes/custom') ?
      'recipes/custom' : 'recipes';

    return $this->drupalContext->getDrupalRoot() . '/' . $recipes_dir;
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

    while (TRUE) {
      $this->io->text('test');
      $question = new Question('Enter the vendor name of this recipe.', 'drupal');
      $vendor_name = $this->io()->askQuestion($question);

      if (!$vendor_name) {
        break;
      }

      $question = new Question('Enter the package name.', 'my-custom-recipe');
      $package_name = $this->io()->askQuestion($question);

      if (!$package_name) {
        break;
      }

      $question = new Question('Enter a description for this recipe.', 'My awesome Drupal recipe.');
      $recipe_description = $this->io()->askQuestion($question);

      $vars['composer']['vendor_name'] = $vendor_name;
      $vars['composer']['package_name'] = $package_name;
      $vars['composer']['recipe_description'] = $recipe_description;
    }

    return $vars['composer'];
  }

}
