<?php
/**
 * This file is part of the BusinessDropCapBundle and it is distributed
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

namespace AlphaLemon\AlphaLemonCmsBundle\Core\Listener\JsonBlock;

use AlphaLemon\AlphaLemonCmsBundle\Core\Event\Actions\Block\BlockEditorRenderingEvent;

/**
 * Provides a base class to render a Json block manager editor
 *
 * @author alphalemon <webmaster@alphalemon.com>
 * 
 * @deprecated since 1.1.0
 * @codeCoverageIgnore
 */
abstract class BaseRenderingEditorListener
{
    /**
     * Configure the options required by the event
     * 
     * Valid options are:
     *      blockClass    [Mandatory] : The class that identifies the Block Manager
     *      formClass     [Mandatory] : The class that represents the form to render
     *      embeddedClass [Optional]  : The embedded class to handle the form's data
     * 
     * @return array
     * 
     * @api
     */
    abstract protected function configure();
    
    /**
     * Renders the editor and adds it to the response replacing the curret response's content
     * 
     * @param \AlphaLemon\AlphaLemonCmsBundle\Core\Event\Actions\Block\BlockEditorRenderingEvent $event
     * @param array $params
     * @throws \InvalidArgumentException
     * @throws \AlphaLemon\AlphaLemonCmsBundle\Core\Listener\JsonBlock\Exception
     * 
     * @api
     */
    abstract protected function renderEditor(BlockEditorRenderingEvent $event, array $params);

    /**
     * Renders the editor
     * 
     * @param \AlphaLemon\AlphaLemonCmsBundle\Core\Event\Actions\Block\BlockEditorRenderingEvent $event
     * @throws \AlphaLemon\AlphaLemonCmsBundle\Core\Listener\JsonBlock\Exception
     * @throws \InvalidArgumentException
     * 
     * @api
     */
    public function onBlockEditorRendering(BlockEditorRenderingEvent $event)
    {
        try {
            $params = $this->configure();

            if (!is_array($params)) {
                throw new \InvalidArgumentException(sprintf('The "configure" method for class "%s" must return an array', get_class($this)));
            }

            if (!array_key_exists('blockClass', $params)) {
                throw new \InvalidArgumentException(sprintf('The array returned by the "configure" method of the class "%s" method must contain the "blockClass" option', get_class($this)));
            }

            if (!class_exists($params['blockClass'])) {
                throw new \InvalidArgumentException(sprintf('The block class "%s" defined in "%s" does not exists', $params['blockClass'], get_class($this)));
            }

            $this->renderEditor($event, $params);
        } catch (\Exception $ex) {
            throw $ex;
        }
    }
}