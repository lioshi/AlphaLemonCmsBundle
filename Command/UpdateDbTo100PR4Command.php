<?php
/*
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

namespace AlphaLemon\AlphaLemonCmsBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;

use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Input\InputOption;
/*
use AlphaLemon\ThemeEngineBundle\Core\ThemeManager\AlThemeManager;
use AlphaLemon\AlphaLemonCmsBundle\Core\Content\Language\AlLanguageManager;
use AlphaLemon\AlphaLemonCmsBundle\Core\Content\Page\AlPageManager;
*/

use AlphaLemon\AlphaLemonCmsBundle\Model\AlContent;
use AlphaLemon\AlphaLemonCmsBundle\Model\AlContentQuery;
use AlphaLemon\AlphaLemonCmsBundle\Model\AlLanguageQuery;
use AlphaLemon\AlphaLemonCmsBundle\Model\AlPageQuery;
use AlphaLemon\AlphaLemonCmsBundle\Model\AlPageAttributeQuery;
use AlphaLemon\PageTreeBundle\Core\Tools\AlToolkit;

use AlphaLemon\AlphaLemonCmsBundle\Model\AlContentVersion;


/**
 * Populates the database after a fresh install
 *
 * @author AlphaLemon <info@alphalemon.com>
 */
class UpdateDbTo100PR4Command extends ContainerAwareCommand
{
    /**
     * @see Command
     */
    protected function configure()
    {
        $this
            ->setDescription('Populates the database with default values. Be careful if you try to run this command on an existind database, because it is resets and repopulates the database itself')
            ->setDefinition(array(
                new InputArgument('dsn', InputArgument::REQUIRED, 'The dsn to connect the database'),
                new InputOption('user', '', InputOption::VALUE_OPTIONAL, 'The database user', 'root'),
                new InputOption('password', null, InputOption::VALUE_OPTIONAL, 'The database password', ''),
            ))
            ->setName('alphalemon:update-db-to-1.0.0.PR4');
    }

    /**
     * @see Command
     * 
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $connection = new \PropelPDO($input->getArgument('dsn'), $input->getOption('user'), $input->getOption('password'));
        
        $sqlFile = AlToolkit::locateResource($this->getContainer(), '@AlphaLemonCmsBundle/Resources/sql_update/1.0.0.PR4.sql');
        $updateQueries = file_get_contents($sqlFile);
        
        $queries = explode(';', $updateQueries);
        foreach($queries as $query)
        {
            $statement = $connection->prepare($query);
            $statement->execute();
        }
        
        $connection->beginTransaction();
        $this->deleteObjects(AlContentQuery::create()->filterByToDelete(1)->find());
        $this->deleteObjects(AlLanguageQuery::create()->filterByToDelete(1)->find());
        $this->deleteObjects(AlPageQuery::create()->filterByToDelete(1)->find());
        $this->deleteObjects(AlPageAttributeQuery::create()->filterByToDelete(1)->find());
        $connection->commit();
        
        AlToolkit::executeCommand($this->getContainer()->get('kernel'), 'propel:build-model');
        
        $alLanguages = AlLanguageQuery::create()->filterByToDelete(0)->find();
        foreach($alLanguages as $alLanguage)
        {
            $values = $alLanguage->toArray();
            $values["Version"] = 1;
            
            $objects = AlContentQuery::create()->filterByAlLanguage($alLanguage)->filterByToDelete(0)->find();
            if(count($objects) > 0)
            {
                $foreignKeys = $this->retrieveForeignKeys($objects);
                $values["AlContentIds"] = $foreignKeys['keys'];
                $values["AlContentVersions"] = $foreignKeys['versions'];
            }
            
            $objects = AlPageAttributeQuery::create()->filterByAlLanguage($alLanguage)->filterByToDelete(0)->find();
            if(count($objects) > 0)
            {
                $foreignKeys = $this->retrieveForeignKeys($objects);
                $values["AlPageAttributeIds"] = $foreignKeys['keys'];
                $values["AlPageAttributeVersions"] = $foreignKeys['versions'];
            }
            
            $version = new \AlphaLemon\AlphaLemonCmsBundle\Model\AlLanguageVersion();
            $version->fromArray($values);
            $version->save();
        }
        
        $alPages = AlPageQuery::create()->filterByToDelete(0)->find();
        foreach($alPages as $alPage)
        {
            $values = $alPage->toArray();
            $values["Version"] = 1;
            
            $objects = AlContentQuery::create()->filterByAlPage($alPage)->filterByToDelete(0)->find();
            if(count($objects) > 0)
            {
                $foreignKeys = $this->retrieveForeignKeys($objects);
                $values["AlContentIds"] = $foreignKeys['keys'];
                $values["AlContentVersions"] = $foreignKeys['versions'];
            }
            
            $objects = AlPageAttributeQuery::create()->filterByAlPage($alPage)->filterByToDelete(0)->find();
            if(count($objects) > 0)
            {
                $foreignKeys = $this->retrieveForeignKeys($objects);
                $values["AlPageAttributeIds"] = $foreignKeys['keys'];
                $values["AlPageAttributeVersions"] = $foreignKeys['versions'];
            }
            
            $version = new \AlphaLemon\AlphaLemonCmsBundle\Model\AlPageVersion();
            $version->fromArray($values);
            $version->save();
        }
        
        $objects = AlPageAttributeQuery::create()->filterByToDelete(0)->find();
        $this->saveObjects($objects, '\AlphaLemon\AlphaLemonCmsBundle\Model\AlPageAttributeVersion', array('PageIdVersion' => '1', 'LanguageIdVersion' => '1'));
        
        $objects = AlContentQuery::create()->filterByToDelete(0)->find();
        $this->saveObjects($objects, '\AlphaLemon\AlphaLemonCmsBundle\Model\AlContentVersion', array('PageIdVersion' => '1', 'LanguageIdVersion' => '1'));
        
        $statement = $connection->prepare('UPDATE al_content SET version = 1');
        $statement->execute();
        $statement = $connection->prepare('UPDATE al_page SET version = 1');
        $statement->execute();
        $statement = $connection->prepare('UPDATE al_page_attribute SET version = 1');
        $statement->execute();
        $statement = $connection->prepare('UPDATE al_language SET version = 1');
        $statement->execute();
    }
    
    private function retrieveForeignKeys($objects)
    {
        $ids = array();        
        $versions = array(); 
        foreach($objects as $object)
        {
            $ids[] = $object->getId();
            $versions[] = 1;
        }
        
        return array('keys' => $ids, 'versions' => $versions);
        return array('keys' => '| ' . implode(' | ', $ids) . ' |', 'versions' => '| ' . implode(' | ', $versions) . ' |');
    }
    
    private function saveObjects($objects, $class, array $versioning = array())
    {
        foreach($objects as $object)
        {
            $values = $object->toArray();
            $values["Version"] = 1;
            $values = array_merge($values, $versioning);
            
            $version = new $class();
            $version->fromArray($values);
            $version->save();
            
            //$object->setVersion(1);
            //$object->save();
        }
    }
    
    private function deleteObjects($objects)
    {
        foreach($objects as $object)
        {
            $object->delete();
        }
    }
}
