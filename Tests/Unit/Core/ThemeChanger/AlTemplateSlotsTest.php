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

namespace AlphaLemon\AlphaLemonCmsBundle\Tests\Unit\Core\ThemeChanger;

use AlphaLemon\AlphaLemonCmsBundle\Tests\TestCase;
use AlphaLemon\AlphaLemonCmsBundle\Core\ThemeChanger\AlTemplateSlots;
use org\bovigo\vfs\vfsStream;

/**
 * AlTemplateSlotsTest
 *
 * @author alphalemon <webmaster@alphalemon.com>
 */
class AlTemplateSlotsTest extends TestCase
{
    protected function setUp()
    {
        parent::setUp();
        
        $this->container = $this->getMock('Symfony\Component\DependencyInjection\ContainerInterface');
        
        $this->factoryRepository = $this->getMock('AlphaLemon\AlphaLemonCmsBundle\Core\Repository\Factory\AlFactoryRepositoryInterface');
        $this->themes = $this->getMock('AlphaLemon\ThemeEngineBundle\Core\ThemesCollection\AlThemesCollectionInterface');
        
        $this->blockRepository = 
            $this->getMockBuilder('AlphaLemon\AlphaLemonCmsBundle\Core\Repository\Propel\AlBlockRepositoryPropel')
                 ->disableOriginalConstructor()
                 ->getMock()
        ;
        
        $this->themes = 
            $this->getMockBuilder('AlphaLemon\ThemeEngineBundle\Core\ThemesCollection\AlThemesCollection')
                 ->disableOriginalConstructor()
                 ->getMock()
        ;
        
        $this->eventsHandler = 
            $this->getMockBuilder('AlphaLemon\AlphaLemonCmsBundle\Core\Event\Content\EventsHandler\AlContentEventsHandler')
                 ->disableOriginalConstructor()
                 ->getMock()
        ;
        
        $this->blockManagerFactory = 
            $this->getMockBuilder('AlphaLemon\AlphaLemonCmsBundle\Core\Content\Block\AlBlockManagerFactory')
                 ->disableOriginalConstructor()
                 ->getMock()
        ;
        
        $this->viewRenderer = $this->getMock('AlphaLemon\AlphaLemonCmsBundle\Core\ViewRenderer\AlViewRendererInterface');
        
        $this->templateSlots = new AlTemplateSlots($this->container);
    }
    
    public function testPreviousThemeStructureFileDoesNotExist()
    {
        $root = vfsStream::setup('root', null, array('Resources' => array()));
        $this->container
             ->expects($this->at(0))
             ->method('getParameter')
             ->with('alpha_lemon_cms.theme_structure_file')
             ->will($this->returnValue(vfsStream::url('root\Resources\.site_structure')))
        ;
        
        $this->templateSlots->run(2, 2);        
        $this->assertEmpty($this->templateSlots->getSlots());
    }
    
    /**
     * @dataProvider runProvider
     */
    public function testRun($slots, $blocks)
    {
        $root = vfsStream::setup('root', null, array('Resources' => array('.site_structure' => '{"Theme":"BootbusinessThemeBundle","Templates":{"2-2":"home"}}')));
        $this->container
             ->expects($this->at(0))
             ->method('getParameter')
             ->with('alpha_lemon_cms.theme_structure_file')
             ->will($this->returnValue(vfsStream::url('root\Resources\.site_structure')))
        ;
    
        $this->container
             ->expects($this->at(1))
             ->method('get')
             ->with('alpha_lemon_cms.factory_repository')
             ->will($this->returnValue($this->factoryRepository))
        ;
        
        $this->container
             ->expects($this->at(2))
             ->method('get')
             ->with('alpha_lemon_theme_engine.themes')
             ->will($this->returnValue($this->themes))
        ;
        
        $this->container
             ->expects($this->at(3))
             ->method('get')
             ->with('alpha_lemon_cms.events_handler') 
             ->will($this->returnValue($this->eventsHandler))
        ;
        
        $this->container
             ->expects($this->at(4))
             ->method('get')
             ->with('alpha_lemon_cms.block_manager_factory')
             ->will($this->returnValue($this->blockManagerFactory))
        ;
        
        $this->container
             ->expects($this->at(5))
             ->method('get')
             ->with('alpha_lemon_cms.view_renderer')
             ->will($this->returnValue($this->viewRenderer))
        ;
        
        $this->viewRenderer
             ->expects($this->any())
             ->method('render')
             ->will($this->returnValue('rendered content'))
        ;
        
        $this->blockRepository
             ->expects($this->any())
             ->method('retrieveContents')
             ->will($this->returnValue($blocks));
        ;
        
        $this->factoryRepository
             ->expects($this->any())
             ->method('createRepository')
             ->with('Block')
             ->will($this->returnValue($this->blockRepository));
        ;
        
        $sequence = 0;
        foreach ($blocks as $block) {
            $this->blockManagerFactory
                 ->expects($this->at($sequence))
                 ->method('createBlockManager')
                 //->with('Block')
                 ->will($this->returnValue($this->initBlockManager($block)));
            ;
            $sequence++;
        }
        
        $template = $this->getMockBuilder('AlphaLemon\ThemeEngineBundle\Core\Template\AlTemplate')
             ->disableOriginalConstructor()
             ->getMock()
        ;
        
        $templateSlots = $this->getMock('AlphaLemon\ThemeEngineBundle\Core\TemplateSlots\AlTemplateSlotsInterface');
        
        $templateSlots->expects($this->any())
                ->method('getSlots')
                ->will($this->returnValue($slots));
        
        $template->expects($this->any())
            ->method('getTemplateSlots')
            ->will($this->returnValue($templateSlots));
        
        $theme = $this->getMockBuilder('AlphaLemon\ThemeEngineBundle\Core\Theme\AlTheme')
             ->disableOriginalConstructor()
             ->getMock()
        ;
        
        $theme
            ->expects($this->once())
            ->method('getTemplate')
            ->with('home')
            ->will($this->returnValue($template))
        ;
        
        
        $themeName = 'BootbusinessThemeBundle';
        $this->themes 
             ->expects($this->at(0))
             ->method('getTheme')
             ->with($themeName)
             ->will($this->returnValue($theme))
        ;     
        /*
        $templateManager = 
            $this->getMockBuilder('AlphaLemon\AlphaLemonCmsBundle\Core\Content\Template\AlTemplateManager')
                 ->disableOriginalConstructor()
                 ->getMock()
        ;
        $this->templateSlots->setTemplateManager($templateManager);*/
        
        $this->templateSlots->run(2, 2);
        
        $this->assertEquals($this->expectedSlots($slots, $blocks), $this->templateSlots->getSlots());
    }
    
    protected function expectedSlots($slots, $blocks)
    {
        $result = array();
        foreach($slots as $slotName => $slot) {
            if ( !array_key_exists($slotName, $blocks)) {
                continue;
            }
            
            $result[$slot->getRepeated()][$slotName] = array("content" => "rendered content", "used" => 2);
        }
        
        return $result;
    }
    
    public function runProvider()
    {
        return array(
            array(
                array(
                    'test' => new \AlphaLemon\ThemeEngineBundle\Core\TemplateSlots\AlSlot('test', array('repeated' => 'page')),
                ),
                array(
                   'test' => $this->initBlock('test'),
                ),
            ),
            array( 
                array(
                    'test' => new \AlphaLemon\ThemeEngineBundle\Core\TemplateSlots\AlSlot('test', array('repeated' => 'page')),
                    'test1' => new \AlphaLemon\ThemeEngineBundle\Core\TemplateSlots\AlSlot('test1', array('repeated' => 'page')),
                ),
                array(
                    'test' => $this->initBlock('test'),
                ),
            ),
            array(
                array(
                    'test' => new \AlphaLemon\ThemeEngineBundle\Core\TemplateSlots\AlSlot('test', array('repeated' => 'page')),
                    'test1' => new \AlphaLemon\ThemeEngineBundle\Core\TemplateSlots\AlSlot('test1', array('repeated' => 'page')),
                ),
                array(
                    'test' => $this->initBlock('test'),
                    'test1' => $this->initBlock('test1'),
                ),
            ),
            array(
                array(
                    'test1' => new \AlphaLemon\ThemeEngineBundle\Core\TemplateSlots\AlSlot('test1', array('repeated' => 'site')),
                    'test2' => new \AlphaLemon\ThemeEngineBundle\Core\TemplateSlots\AlSlot('test2', array('repeated' => 'language')),
                    'test' => new \AlphaLemon\ThemeEngineBundle\Core\TemplateSlots\AlSlot('test', array('repeated' => 'page')),
                ),
                array(
                    'test' => $this->initBlock('test'),
                    'test1' => $this->initBlock('test1'),
                    'test2' => $this->initBlock('test2'),
                ),
            ),
            array(
                array(
                    'test1' => new \AlphaLemon\ThemeEngineBundle\Core\TemplateSlots\AlSlot('test1', array('repeated' => 'site')),
                    'test2' => new \AlphaLemon\ThemeEngineBundle\Core\TemplateSlots\AlSlot('test2', array('repeated' => 'language')),
                    'test' => new \AlphaLemon\ThemeEngineBundle\Core\TemplateSlots\AlSlot('test', array('repeated' => 'page')),
                    'test3' => new \AlphaLemon\ThemeEngineBundle\Core\TemplateSlots\AlSlot('test3', array('repeated' => 'page')),
                ),
                array(
                    'test' => $this->initBlock('test'),
                    'test1' => $this->initBlock('test1'),
                    'test2' => $this->initBlock('test2'),
                    'test3' => $this->initBlock('test3'),
                ),
            ),
        );
    }
    
    protected function initBlockManager($block/*, $result, $value = null*/)
    {
        $blockManager = 
            $this->getMockBuilder('AlphaLemon\AlphaLemonCmsBundle\Core\Content\Block\ServiceBlock\AlBlockManagerService')
                 ->disableOriginalConstructor()
                 ->getMock()
        ;
        
        $blockManager 
             ->expects($this->once())
             ->method('setEditorDisabled')
             ->with(true)
             ->will($this->returnSelf());
        ;
        
        $blockManager 
             ->expects($this->once())
             ->method('get')
             ->will($this->returnValue($block));
        ;
        
        return $blockManager;
    }
    
    protected function initBlock($slotName)
    {
        $block = $this->getMock('AlphaLemon\AlphaLemonCmsBundle\Model\AlBlock');
        
        $block
            ->expects($this->once())
            ->method('getSlotName')
            ->will($this->returnValue($slotName))
        ;
        
        $block
            ->expects($this->once())
            ->method('getToDelete')
            ->will($this->returnValue(2))
        ;
        
        return $block;
    }
}
