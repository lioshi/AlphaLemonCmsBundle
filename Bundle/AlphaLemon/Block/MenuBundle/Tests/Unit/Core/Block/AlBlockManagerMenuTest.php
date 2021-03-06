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
          
namespace AlphaLemon\Block\MenuBundle\Tests\Unit\Core\Block;;


/**
 * AlBlockManagerMenuTest
 *
 * @author AlphaLemon <webmaster@alphalemon.com>
 */
class AlBlockManagerMenuTest extends BaseBlockManagerMenu
{
    protected function setUp()
    {
        parent::setUp();
        
        $this->blocksTemplate = 'MenuBundle:Content:menu.html.twig';
    }
    
    protected function getBlockManager()
    {
        return new \AlphaLemon\Block\MenuBundle\Core\Block\AlBlockManagerMenu($this->container, $this->validator);
    }
}
