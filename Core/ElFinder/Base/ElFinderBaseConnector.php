<?php
/**
 * This file is part of the AlphaLemon CMS Application and it is distributed
 * under the GPL LICENSE Version 2.0. To use this application you must leave
 * intact this copyright notice.
 *
 * Copyright (c) AlphaLemon <webmaster@alphalemon.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * For extra documentation and help please visit http://www.alphalemon.com
 *
 * @license    GPL LICENSE Version 2.0
 *
 */

namespace AlphaLemon\AlphaLemonCmsBundle\Core\ElFinder\Base;

use AlphaLemon\ElFinderBundle\Core\Connector\AlphaLemonElFinderBaseConnector;
use AlphaLemon\AlphaLemonCmsBundle\Core\AssetsPath\AlAssetsPath;

/**
 * Configures the ElFinder library to manage media files, like images, flash, pdf and more
 */
abstract class ElFinderBaseConnector extends AlphaLemonElFinderBaseConnector
{
    /**
     * {@inheritdoc}
     */
    protected function generateOptions($folder, $rootAlias)
    {
        $assetsPath = $this->container->getParameter('alpha_lemon_cms.upload_assets_full_path') . '/' . $folder;
        if (!is_dir($assetsPath)) {
            @mkdir($assetsPath);
        }
        
        $request = $this->container->get('request');        
        $options = array(
            'locale' => '',
            'roots' => array(
                array(
                    'driver'        => 'LocalFileSystem',   // driver for accessing file system (REQUIRED)
                    'path'          => $assetsPath,         // path to files (REQUIRED)
                    'URL'           => $request->getScheme().'://'.$request->getHttpHost() . '/' . AlAssetsPath::getUploadFolder($this->container) . '/' . $folder, // URL to files (REQUIRED)
                    'accessControl' => 'access',             // disable and hide dot starting files (OPTIONAL)
                    'rootAlias'     => $rootAlias             // disable and hide dot starting files (OPTIONAL)
                )
            )
        );

        return $options;
    }
}
