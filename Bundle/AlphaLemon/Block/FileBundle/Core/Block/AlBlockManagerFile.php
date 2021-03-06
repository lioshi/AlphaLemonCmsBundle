<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

namespace AlphaLemon\Block\FileBundle\Core\Block;

use AlphaLemon\AlphaLemonCmsBundle\Core\Content\Block\JsonBlock\AlBlockManagerJsonBlockContainer;
use AlphaLemon\ThemeEngineBundle\Core\Asset\AlAsset;
use AlphaLemon\AlphaLemonCmsBundle\Core\AssetsPath\AlAssetsPath;

/**
 * Description of AlBlockManagerFile
 *
 * @author alphalemon <webmaster@alphalemon.com>
 */
class AlBlockManagerFile extends AlBlockManagerJsonBlockContainer
{
    public function getDefaultValue()
    {
        $value =
        '{
            "0" : {
                "file" : "Click to load a file",
                "opened" : false
            }
        }';

        return array(
            'Content' => $value,
        );
    }
    
    protected function renderHtml()
    {
        $items = $this->decodeJsonContent($this->alBlock);
        $item = $items[0];
        $file = $item['file'];
        
        $kernel = $this->container->get('kernel');
        $deployBundle = $this->container->getParameter('alpha_lemon_theme_engine.deploy_bundle');
        $deployBundleAsset = new AlAsset($kernel, $deployBundle);

        return ($item['opened'])
            ? sprintf("{%% set file = kernel_root_dir ~ '/../" . $this->container->getParameter('alpha_lemon_cms.web_folder') . "/%s/%s' %%} {{ file_open(file) }}", $deployBundleAsset->getAbsolutePath(), $file)
            : sprintf('<a href="/%s/%s" />%s</a>', AlAssetsPath::getUploadFolder($this->container), $file, basename($file));        
    }
        
    public function editorParameters()
    {        
        $items = $this->decodeJsonContent($this->alBlock);
        $item = $items[0];
        $item['opened'] = $this->itemOpenedToBool($item);
             
        $formClass = $this->container->get('file.form');
        $form = $this->container->get('form.factory')->create($formClass, $item); 
        
        return array(
            "template" => 'FileBundle:Editor:file_editor.html.twig',
            "title" => "Files editor",
            'form' => $form->createView(),
        );
    }
    
    public function getHideInEditMode()
    {
        return true;
    }
    
    protected function replaceHtmlCmsActive()
    {
        $options = $this->getOptions();   

        return array('RenderView' => array(
            'view' => 'FileBundle:Content:file.html.twig',
            'options' => $options,
        ));
    }
    
    private function getOptions()
    {
        $items = $this->decodeJsonContent($this->alBlock);
        $item = $items[0];
        $item['opened'] = $this->itemOpenedToBool($item);
        $file = $item['file'];
        
        $options = ($item['opened'])
            ? 
                array(
                    'webfolder' => $this->container->getParameter('alpha_lemon_cms.web_folder'),
                    'folder' => AlAssetsPath::getUploadFolder($this->container),
                    'filename' => $file,
                )
            :
                array(
                    'webfolder' => $this->container->getParameter('alpha_lemon_cms.web_folder'),
                    'folder' => AlAssetsPath::getUploadFolder($this->container),
                    'filename' => $file,
                    'filepath' => basename($file),
                )
        ;
        
        return $options;
    }
    
    private function itemOpenedToBool($item)
    {
        return array_key_exists('opened', $item) ? filter_var($item['opened'], FILTER_VALIDATE_BOOLEAN) : false;
    }
}
