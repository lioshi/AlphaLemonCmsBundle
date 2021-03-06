<?php

namespace AlphaLemon\AlphaLemonCmsBundle\Core\Generator;

use Sensio\Bundle\GeneratorBundle\Generator\BundleGenerator;
use Symfony\Component\DependencyInjection\Container;
use AlphaLemon\ThemeEngineBundle\Core\Generator\AlBaseGenerator;

/**
 * AlAppBlockGenerator generates an App-Block bundle
 *
 * @author alphalemon <webmaster@alphalemon.com>
 * 
 * @api
 */
class AlAppBlockGenerator extends AlBaseGenerator
{
    /**
     * {@inheritdoc}
     */
    public function generateExt($namespace, $bundle, $dir, $format, $structure, array $options)
    {
        $format = 'annotation';
        $this->generate($namespace, $bundle, $dir, $format, $structure);

        $dir .= '/'.strtr($namespace, '\\', '/');
        $bundleBasename = str_replace('Bundle', '', $bundle);

        $this->filesystem->mkdir($dir.'/Core/Block');

        $blockSkeletonDir = __DIR__ . '/../../Resources/skeleton/app-block';
        $extensionAlias = Container::underscore($bundleBasename);
        $typeLowercase = strtolower($bundleBasename);        
        $parameters = array(
            'namespace' => $namespace,
            'namespace_path' => str_replace('\\', '\\\\', $namespace),
            'target_dir' => str_replace('\\', '/', $namespace),
            'bundle'    => $bundle,
            'format'    => $format,
            'bundle_basename' => $bundleBasename,
            'type_lowercase' => $typeLowercase,
            'extension_alias' => $extensionAlias,
            'description'    => $options["description"],
            'group'    => $options["group"],
        ); 
        
        $this->renderFile($blockSkeletonDir, 'Block.php', $dir.'/Core/Block/AlBlockManager'.$bundleBasename.'.php', $parameters);
        $this->renderFile($blockSkeletonDir, 'FormType.php', $dir.'/Core/Form/'.$bundleBasename.'Type.php', $parameters);
        $this->renderFile($blockSkeletonDir, 'app_block.xml', $dir.'/Resources/config/app_block.xml', $parameters);
        $this->renderFile($blockSkeletonDir, 'config_alcms.yml', $dir.'/Resources/config/config_alcms.yml', $parameters);
        $this->renderFile($blockSkeletonDir, 'config_alcms_dev.yml', $dir.'/Resources/config/config_alcms_dev.yml', $parameters);
        $this->renderFile($blockSkeletonDir, 'config_alcms_test.yml', $dir.'/Resources/config/config_alcms_test.yml', $parameters);
        $this->renderFile($blockSkeletonDir, 'autoload.json', $dir.'/autoload.json', $parameters);
        if (!array_key_exists("no-strict", $options) || $options["no-strict"] == false) {
            $this->renderFile($blockSkeletonDir, 'composer.json', $dir.'/composer.json', $parameters);
        }
        $this->filesystem->copy($blockSkeletonDir . '/block.html.twig', $dir.'/Resources/views/Content/' . $typeLowercase . '.html.twig');
        $this->filesystem->copy($blockSkeletonDir . '/form_editor.html.twig', $dir.'/Resources/views/Editor/' . $typeLowercase . '.html.twig');
    }
}
