You can lock frontend access during upgrade process.
To do this, you must update your index.php file and configure errors template.

First, open index.php file:

## replace ##
if (file_exists($maintenanceFile)) {
    include_once dirname(__FILE__).'/errors/503.php';
    exit;
}
## by ##
// https://redmine.luigifab.info/projects/magento/wiki/versioning
// update /admin/ by your admin key
if (file_exists('maintenance.flag') && (strpos(getenv('REQUEST_URI'), '/admin/') === false)) {
    $ipFile = './errors/versioning/config/503.ip';
    if (!is_file($ipFile) || (is_file($ipFile) && (strpos(file_get_contents($ipFile), '-'.getenv('REMOTE_ADDR').'-') === false))) {
        include_once('./errors/versioning/503.php');
        exit;
    }
}
if (file_exists('upgrade.flag') && (strpos(getenv('REQUEST_URI'), '/admin/') === false)) {
    $ipFile = './errors/versioning/config/upgrade.ip';
    if (!is_file($ipFile) || (is_file($ipFile) && (strpos(file_get_contents($ipFile), '-'.getenv('REMOTE_ADDR').'-') === false))) {
        include_once('./errors/versioning/upgrade.php');
        exit;
    }
}
## end ##

Secondly, copy errors/local.xml.sample to errors/local.xml.
Open it, replace <skin>default</skin> by <skin>versioning</skin> and save it.

That's it.
To change CSS, write your rules into 'errors/versioning/config/user.css' file.
