## Drupal Recipe Generator

This add-on for Drush adds the capability to generate a Recipe for Drupal to
help you build out your own Recipe with an interactive tool. The
Distributions and Recipes initiative is still in development.

https://git.drupalcode.org/project/distributions_recipes

After installing with Composer, run the command with the following:

```shell
drush gen recipe
```

You will have a series of prompts to help you craft your Recipe.

The output will be placed into (webroot)/recipes/custom. You can optionally
declare a composer.json file with dependencies which will also be located
here as part of the generated output.

The end result will generate a recipe.yml file based on your input, for example:

```yaml
name: 'My Custom Recipe'
description: 'this is the description'
type: 'Site'

install:
  - node
  - user
  - redirect
  - gin
  - pathauto
  - dblog
  - views

config:
  import:
    dblog: '*'
    pathauto: '*'
    redirect:
      - redirect.settings
      - system.action.redirect_delete_action
      - views.view.redirect
    node:
      - views.view.content
    user:
      - views.view.user_admin_people
    gin:
      - gin.settings
      - block.block.gin_breadcrumbs
      - block.block.gin_content
      - block.block.gin_local_actions
      - block.block.gin_messages
      - block.block.gin_page_title
      - block.block.gin_primary_local_tasks
      - block.block.gin_secondary_local_tasks
```
