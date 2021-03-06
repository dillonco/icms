<?php
/**
 * SiteModel
 * For general functionality of a CMS
 *
 * @package ICMS
 * @author Dillon Aykac
 */
namespace Nixhatter\ICMS\model;

defined('_ICMS') or die;

use Respect\Validation\Validator as v;
class SiteModel extends Model {
    public $text;
    public $posts;
    public $container;
    public $settings;

    public function __construct(\Pimple\Container $container) {
        $this->container = $container;
        $this->db = $container['db'];
        $blog        = new BlogModel($container);
        $this->settings = $container['settings'];
        $this->posts        =$blog->get_posts();
    }

    public function getTemplatePath($template)
    {
        $directory = 'templates/'.$template.'/';
        if (v::directory()->validate($directory)){
            return $directory;
        } else {
            // Revert to default if template not found
            return "templates/default/";
        }
    }

    public function editTemplate($file, $content)
    {
        try {
            if(file_put_contents($file, $content)) {
                return true;
            } else {
                return false;
            }
        } catch (\PDOException $e) {
            exit($e->getMessage());
        }
    }

    /**
     * @param $oldSetting The full setting as seen in the config file
     * @param $newSetting The entire line that will replace the old one
     * Source: http://stackoverflow.com/questions/3004041/how-to-replace-a-particular-line-in-a-text-file-using-php
     */
    public function editConfig($config, $newSetting) {
        $config_file = "../core/configuration.php";
        $config_tmp = "../core/configuration.tmp";
        if (v::alpha('.')->validate($config) && file_exists($config_file)
        && is_writable("../core/")) {
            $reading = fopen($config_file, 'r');
            $writing = fopen($config_tmp, 'w');

            $replaced = false;

            while (!feof($reading)) {
                $line = fgets($reading);
                if (stristr($line, $config)) {
                    $line = $config . " = \"" . $newSetting . "\"\r\n";
                    $replaced = true;
                }
                fputs($writing, $line);
            }
            fclose($reading);
            fclose($writing);
            // might as well not overwrite the file if we didn't replace anything
            if ($replaced) {
                rename($config_tmp, $config_file);
            } else {
                unlink($config_tmp);
            }
        }
    }

    public function hasConfigChanged($config1, $config2, $newSetting)
    {
        if (v::alpha()->validate($config1) && v::alpha()->validate($config2)) {
            $oldSetting = $this->settings["production"][$config1][$config2];
            if (!empty($newSetting) && $newSetting != $oldSetting) {
                $combinedConfig = $config1 . "." . $config2;
                $this->editConfig($combinedConfig, $newSetting);
                return true;
            } else {
                return false;
            }
        }
    }
}
