<?php
/**
 * Loading Slots
 *
 * @author Tim Lochmüller
 */

namespace HDNET\Autoloader\Loader;

use HDNET\Autoloader\Loader;
use HDNET\Autoloader\LoaderInterface;
use TYPO3\CMS\Core\Utility\ExtensionManagementUtility;
use TYPO3\CMS\Core\Utility\GeneralUtility;

/**
 * Loading Slots
 */
class StaticTyposcript implements LoaderInterface
{

    /**
     * Get all the complex data for the loader.
     * This return value will be cached and stored in the database
     * There is no file monitoring for this cache
     *
     * @param Loader $loader
     * @param int    $type
     *
     * @return array
     */
    public function prepareLoader(Loader $loader, $type)
    {
        $tsConfiguration = [];
        $extPath = ExtensionManagementUtility::extPath($loader->getExtensionKey());
        $baseDir = $extPath . 'Configuration/TypoScript/';
        if (!is_dir($baseDir)) {
            return $tsConfiguration;
        }
        $typoScriptFolder = GeneralUtility::getAllFilesAndFoldersInPath([], $baseDir, '', true, 99, '(.*)\\.(.*)');
        $extensionName = GeneralUtility::underscoredToUpperCamelCase($loader->getExtensionKey());

        foreach ($typoScriptFolder as $folder) {
            if (is_file($folder . 'setup.txt') || is_file($folder . 'constants.txt')) {
                $setupName = $extensionName . '/' . str_replace($baseDir, '', $folder);
                $setupName = implode(' - ', GeneralUtility::trimExplode('/', $setupName, true));
                $folder = str_replace($extPath, '', $folder);
                $tsConfiguration[] = [
                    'path'  => $folder,
                    'title' => $setupName,
                ];
            }
        }

        return $tsConfiguration;
    }

    /**
     * Run the loading process for the ext_tables.php file
     *
     * @param Loader $loader
     * @param array  $loaderInformation
     *
     * @return NULL
     */
    public function loadExtensionTables(Loader $loader, array $loaderInformation)
    {
        foreach ($loaderInformation as $tsConfig) {
            ExtensionManagementUtility::addStaticFile($loader->getExtensionKey(), $tsConfig['path'], $tsConfig['title']);
        }
        return null;
    }

    /**
     * Run the loading process for the ext_localconf.php file
     *
     * @param Loader $loader
     * @param array  $loaderInformation
     *
     * @return NULL
     */
    public function loadExtensionConfiguration(Loader $loader, array $loaderInformation)
    {
        return null;
    }
}
