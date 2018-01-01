First, for the gitignore file:

/errors/config/*.ip
/errors/config/*.dat
/errors/config/*.csv
/maintenance.flag
/upgrade.flag

Secondly, for the index.php file:

## replace ##
if (file_exists($maintenanceFile)) {
    include_once dirname(__FILE__).'/errors/503.php';
    exit;
}
## by ##
// https://www.luigifab.info/magento/versioning
if (is_file('./maintenance.flag') && (strpos(getenv('REQUEST_URI'), '/admin/') === false)) {
    $ips = './errors/config/error503.ip';
    if (!is_file($ips) || (is_file($ips) && (strpos(file_get_contents($ips), '-'.getenv('REMOTE_ADDR').'-') === false))) {
        include_once('./errors/503.php');
        exit(0);
    }
}
if (is_file('./upgrade.flag') && (strpos(getenv('REQUEST_URI'), '/admin/') === false)) {
    $ips = './errors/config/upgrade.ip';
    if (!is_file($ips) || (is_file($ips) && (strpos(file_get_contents($ips), '-'.getenv('REMOTE_ADDR').'-') === false))) {
        include_once('./errors/upgrade.php');
        exit(0);
    }
}
## end ##


You can rewrite files:
 - errors/processor.php       » errors/config/processor.php  (use UserProcessor as class name, your class must extends Processor)
 - errors/page.phtml          » errors/config/page.phtml

The errors/config directory will contains text files.
They will be automatically created when you will save the module configuration in the backend:
 - errors/config/error503.ip  » list of ip addresses
 - errors/config/upgrade.ip   » list of ip addresses
 - errors/config/config.dat   » configuration
 - errors/config/*.csv        » translations
