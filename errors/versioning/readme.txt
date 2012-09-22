You can lock frontend access during upgrade process.
To do this, you must update your index.php file and configure errors template.

First, open index.php file:

# replace
if (file_exists($maintenanceFile)) {
    include_once dirname(__FILE__).'/errors/503.php';
    exit;
}

# by
// https://redmine.luigifab.info/projects/magento/wiki/versioning
// update /admin/ by your admin key
if (file_exists('maintenance.flag') && (strpos($_SERVER['REQUEST_URI'], '/admin/') === false)) {
    include_once(dirname(__FILE__).'/errors/versioning/503.php');
    exit;
}
if (file_exists('upgrade.flag') && (strpos($_SERVER['REQUEST_URI'], '/admin/') === false)) {
    include_once(dirname(__FILE__).'/errors/versioning/upgrade.php');
    exit;
}

Secondly, copy errors/local.xml.sample to errors/local.xml.
Open it, replace <skin>default</skin> by <skin>versioning</skin> and save it.

That's it.
