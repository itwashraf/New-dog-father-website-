=== Dog Father Plugin (deploy branch) ===

This branch holds the plugin files at its ROOT, so cPanel can pull it
straight into a named plugin folder.

cPanel setup:
  Clone URL       : https://github.com/itwashraf/New-dog-father-website-.git
  Repository Path : /home/USERNAME/public_html/wp-content/plugins/dog-father-control-center
  Branch          : plugins-deploy

When the plugin code is added, the main plugin file (with the WordPress
plugin header) must live at the root of this branch, e.g.:

  dog-father-control-center.php   <- plugin header here
  includes/
  assets/

NOTE: No plugin code exists in the repository yet. Add the plugin source
on the editing branch under plugins/, then it will be synced here.
