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

namespace AlphaLemon\Block\MenuBundle\Tests\Unit\Core\Block;

use AlphaLemon\AlphaLemonCmsBundle\Tests\Unit\Core\Content\Block\Base\AlBlockManagerContainerBase;
use AlphaLemon\Block\MenuBundle\Core\Block\AlBlockManagerMenu;

/**
 * BaseBlockManagerMenu
 *
 * @author AlphaLemon <webmaster@alphalemon.com>
 */
abstract class BaseBlockManagerMenu extends AlBlockManagerContainerBase
{
    protected $blocksTemplate;
    
    abstract protected function getBlockManager();
            
    public function testDefaultValue()
    {
        $expectedValue = array(
            'Content' => '
            {
                "0": {
                    "blockType" : "Link"
                },
                "1": {
                    "blockType" : "Link"
                }
            }'
        );

        $this->initContainer();
        $blockManager = $this->getBlockManager(); 
        $this->assertEquals($expectedValue, $blockManager->getDefaultValue());
    }
    
    public function testHtmlViewOutput()
    {
        $blockContent = 
            '{
                "0": {
                    "blockType" : "Link"
                },
                "1": {
                    "blockType" : "Link"
                }
            }';

        $this->initContainer();
        $block = $this->initBlock($blockContent);
        $blockManager = $this->getBlockManager();
        $blockManager->set($block);
        
        $expectedResult = array('RenderView' => array(
            'view' => $this->blocksTemplate,
            'options' => array(
                'items' => array (
                    array(
                        "blockType" => "Link"
                    ),
                    array(
                        "blockType" => "Link"
                    ),
                ),
                'block_manager' => $blockManager
            ),
        ));
        
        $this->assertEquals($expectedResult, $blockManager->getHtml());
    }
    
    public function testAddItem()
    {
        $blockContent = 
            '{
                "0": {
                    "blockType" : "Link"
                },
                "1": {
                    "blockType" : "Link"
                }
            }';
        $block = $this->initBlock($blockContent);
        $this->initBlockManager($block);
                
        $blockManager = $this->getBlockManager();
        $blockManager->set($block);
        
        $values = array("Content" => 
            '{
                "operation": "add",
                "value": "Link"
            }',
        );
        $this->assertTrue($blockManager->save($values));
    }
    
    public function testDeleteItem()
    {
        $blockContent = 
            '{
                "0": {
                    "blockType" : "Link"
                },
                "1": {
                    "blockType" : "Link"
                }
            }';
        $block = $this->initBlock($blockContent);
        $this->initBlockManager($block);
        
        $this->blockRepository->expects($this->once())
            ->method('deleteIncludedBlocks')
        ;
                
        $blockManager = $this->getBlockManager();
        $blockManager->set($block);
        
        $values = array("Content" => 
            '{
                "operation": "remove",
                "slotName": "logo",
                "item": "0"
            }',
        );
        $this->assertTrue($blockManager->save($values));
    }
    
    protected function initBlockManager($block)
    {
        $this->blockRepository = $this->getMockBuilder('AlphaLemon\AlphaLemonCmsBundle\Core\Repository\Propel\AlBlockRepositoryPropel')
                                    ->disableOriginalConstructor()
                                    ->getMock();
        
        $this->blockRepository->expects($this->once())
                ->method('setRepositoryObject')
                ->with($block)
                ->will($this->returnSelf());
        
        $this->blockRepository->expects($this->once())
            ->method('startTransaction');

        $this->blockRepository->expects($this->once())
            ->method('commit');

        $this->blockRepository->expects($this->never())
            ->method('rollback');

        $this->blockRepository->expects($this->once())
                ->method('save')
                ->will($this->returnValue(true));
        
        $this->factoryRepository = $this->getMock('AlphaLemon\AlphaLemonCmsBundle\Core\Repository\Factory\AlFactoryRepositoryInterface');
        $this->factoryRepository->expects($this->any())
            ->method('createRepository')
            ->will($this->returnValue($this->blockRepository));
        
        $this->initContainer();
        $event = $this->getMock('AlphaLemon\AlphaLemonCmsBundle\Core\Event\Content\Block\BeforeBlockEditingEvent');
        $this->setUpEventsHandler($event, 2);
    }
    
    private function initBlock($value)
    {
        $block = $this->getMock('AlphaLemon\AlphaLemonCmsBundle\Model\AlBlock');
        $block->expects($this->any())
              ->method('getContent')
              ->will($this->returnValue($value));
        
        $block->expects($this->any())
              ->method('getId')
              ->will($this->returnValue(2));

        return $block;
    }
}
