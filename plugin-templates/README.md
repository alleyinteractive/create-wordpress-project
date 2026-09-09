# Plugin Templates

The plugin-templates folder should contain subfolder that should be copied to the plugin folder after it is scaffolded. Search and replace will be run on the files after they are copied over.

Currently the subfolders are
* blocks
* config
* entries
* features

Everything here is copied except this README and `features.txt`, which only
apply to this repository: `features.txt` is read directly by the configure
script and written into the plugin's main function. READMEs inside the
subfolders are copied along with them.

Use the following placeholders in your files, and they will automatically get updated to the correct values to match the destination plugin.

* `create-wordpress-plugin`
* `Create WordPress Plugin`
* `CREATE_WORDPRESS_PLUGIN`
* `create_wordpress_plugin`
* `Create_WordPress_Plugin`
