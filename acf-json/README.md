# ACF JSON Synchronization

This directory is used by Advanced Custom Fields (ACF) for field group synchronization.

## How it works

1. When field groups are created or modified through the WordPress admin, ACF will save the JSON definitions in this directory.
2. When the plugin is installed on a new site, ACF will read the JSON files from this directory and import the field groups.
3. This ensures that your field definitions stay consistent across different environments.

Do not delete this directory, as it's required for the proper functioning of field synchronization.