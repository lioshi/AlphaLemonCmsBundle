<?php
/**
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

namespace AlphaLemon\Block\FileBundle\Core\ElFinder;

use AlphaLemon\AlphaLemonCmsBundle\Core\ElFinder\Base\ElFinderBaseConnector;

/**
 * Description of ElFinderMarkdownConnector
 *
 * @author alphalemon <webmaster@alphalemon.com>
 */
class ElFinderFileConnector extends ElFinderBaseConnector
{
    protected function configure()
    {
        $filesFolder = $this->container->getParameter('file.base_folder') ;
        
        return $this->generateOptions($filesFolder, 'Files');
    }
}