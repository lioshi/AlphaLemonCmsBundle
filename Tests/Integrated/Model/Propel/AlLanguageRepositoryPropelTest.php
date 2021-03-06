<?php
/**
 * This file is part of the AlphaLemon CMS Application and it is distributed
 * under the GPL LICENSE Version 2.0. To use this application you must leave
 * intact this copyright notice.
 *
 * Copyright (c) AlphaLemon <webmaster@alphalemon.com>
 *
 * For the full copyright and license inflanguageRepositoryation, please view the LICENSE
 * file that was distributed with this source code.
 *
 * For extra documentation and help please visit http://www.alphalemon.com
 *
 * @license    GPL LICENSE Version 2.0
 *
 */

namespace AlphaLemon\AlphaLemonCmsBundle\Tests\Integrated\Model\Propel;

/**
 * AlLanguageRepositoryPropelTest
 *
 * @author alphalemon <webmaster@alphalemon.com>
 */
class AlLanguageRepositoryPropelTest extends Base\BaseModelPropel
{
    private $languageRepository;

    protected function setUp()
    {
        parent::setUp();

        $container = $this->client->getContainer();
        $factoryRepository = $container->get('alpha_lemon_cms.factory_repository');
        $this->languageRepository = $factoryRepository->createRepository('Language');
    }

    /**
     * @expectedException AlphaLemon\AlphaLemonCmsBundle\Core\Exception\Content\General\InvalidParameterTypeException
     * @expectedExceptionMessage AlLanguageRepositoryPropel accepts only AlLanguage propel objects
     */
    public function testRepositoryAcceptsOnlyAlLanguageObjects()
    {
        $this->languageRepository->setRepositoryObject(new \AlphaLemon\AlphaLemonCmsBundle\Model\AlPage());
    }

    public function testALanguageIsRetrievedFromItsPrimaryKey()
    {
        $language = $this->languageRepository->fromPk(2);
        $this->assertInstanceOf('\AlphaLemon\AlphaLemonCmsBundle\Model\AlLanguage', $language);
        $this->assertEquals(2, $language->getId());
    }

    public function testFetchActiveLangues()
    {
        $languages = $this->languageRepository->activeLanguages();
        $this->assertEquals(2, count($languages));
    }

    public function testLanguageIsNullWhenANullValueIsGiven()
    {
        $language = $this->languageRepository->fromLanguageName(null);
        $this->assertNull($language);
    }

    /**
     * @expectedException \InvalidArgumentException
     */
    public function testAnExceptionIsThrownWhenTheGivenParameterIsNotString()
    {
        $this->languageRepository->fromLanguageName(array('en'));
    }

    public function testTheLanguageIsRetrieved()
    {
        $languageName = 'es';
        $language = $this->languageRepository->fromLanguageName($languageName);
        $this->assertEquals($languageName, $language->getLanguageName());
    }

    public function testTheMainLanguageIsRetrieved()
    {
        $language = $this->languageRepository->mainLanguage();
        $this->assertEquals('en', $language->getLanguageName());
    }

    public function testTheFirstLanguageIsRetrieved()
    {
        $language = $this->languageRepository->firstOne();
        $this->assertEquals('en', $language->getLanguageName());
    }
}