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

namespace AlphaLemon\AlphaLemonCmsBundle\Core\Listener\ImagesBlock;

use Symfony\Component\Templating\EngineInterface;
use Symfony\Component\HttpFoundation\Response;
use AlphaLemon\AlphaLemonCmsBundle\Core\Event\Actions\Block\BlockEditedEvent;

/**
 * Renders the editor to manage a collection of images
 *
 * @author alphalemon <webmaster@alphalemon.com>
 * 
 * @deprecated since 1.1.0
 * @codeCoverageIgnore
 */
abstract class BaseImagesBlockEditedListener
{
    protected $templateEngine;

    protected abstract function configure();
    
    /**
     * Contructor
     * 
     * @param \Symfony\Component\Templating\EngineInterface $templateEngine
     * 
     * @api
     */
    public function __construct(EngineInterface $templateEngine)
    {
        $this->templateEngine = $templateEngine;
    }

    /**
     * Renders the editor
     * 
     * @param \AlphaLemon\AlphaLemonCmsBundle\Core\Event\Actions\Block\BlockEditedEvent $event
     * 
     * @api
     */
    public function onBlockEdited(BlockEditedEvent $event)
    {return;
        $blockManager = $event->getBlockManager();
        $blockType = $blockManager->get()->getType();
        if ($blockType == $this->getManagedBlockType()) {
            
            $templateName = 'AlphaLemonCmsBundle:Block:Images/images_list.html.twig';
            $options = $this->configure();
            if (array_key_exists('images_editor_template', $options)) {
                $templateName = $options['images_editor_template'];
            }
            
            $block = $blockManager->get();
            $items = json_decode($block->getContent(), true);
            $form = $this->setUpForm($block->getId(), -1);
            
            $template = $this->templateEngine->render($templateName, array("alContent" => $blockManager, 'items' => $items, 'form' => $form));
            $values = array(
                array("key" => "images-list", "value" => $template),
                array("key" => "message", "value" => "The content has been successfully edited"),
                array("key" => "edit-block",
                        "blockName" => "block_" . $blockManager->get()->getId(),
                        "value" => $this->templateEngine->render('AlphaLemonCmsBundle:Cms:render_block.html.twig', array("block" => $blockManager->toArray()))
                    )
                );

            $response = new Response(json_encode($values));
            $response->headers->set('Content-Type', 'application/json');
            $event->setResponse($response);
        }
    }
    
    protected function setUpForm($blockId, $itemId)
    {
        $item = null;
        $block = $this->fetchBlock($blockId);
        if ($itemId != -1) {
            $content = json_decode($block->getContent(), true);

            if (!array_key_exists($itemId, $content)) {
                throw new \InvalidArgumentException('It seems that the item requested does not exist anymore');
            }

            $item = $content[$itemId];
            $item['id'] = $itemId;
        }

        $formName = sprintf('%s.form', strtolower($block->getType()));
        $formClass = $this->container->get($formName);

        return $this->container->get('form.factory')->create($formClass, $item);
    }
    
    protected function fetchBlock($blockId)
    {
        $factoryRepository = $this->container->get('alpha_lemon_cms.factory_repository');
        $repository = $factoryRepository->createRepository('Block');
        $block = $repository->fromPk($blockId);

        if (null == $block) {
            throw new \InvalidArgumentException('It seems that the block to edit does not exist anymore');
        }

        return $block;
    }
}