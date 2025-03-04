<?php

namespace B13\Menus\Tests\Functional\Domain\Repository;

/*
 * This file is part of TYPO3 CMS-based extension "menus" by b13.
 *
 * It is free software; you can redistribute it and/or modify it under
 * the terms of the GNU General Public License, either version 2
 * of the License, or any later version.
 */

use B13\Menus\Domain\Repository\MenuRepository;
use Psr\EventDispatcher\EventDispatcherInterface;
use TYPO3\CMS\Core\Context\Context;
use TYPO3\CMS\Core\Context\LanguageAspect;
use TYPO3\CMS\Core\Domain\Repository\PageRepository;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\TestingFramework\Core\Functional\FunctionalTestCase;

class MenuRepositoryTest extends FunctionalTestCase
{
    protected array $testExtensionsToLoad = ['typo3conf/ext/menus'];

    /**
     * @test
     */
    public function translatedPageIsNotInMenuIfNavHideIsSet(): void
    {
        $this->importCSVDataSet(ORIGINAL_ROOT . 'typo3conf/ext/menus/Tests/Functional/Domain/Repository/Fixtures/translated_page_with_nav_hide.csv');
        $languageAspect = GeneralUtility::makeInstance(LanguageAspect::class, 1);
        $context = GeneralUtility::makeInstance(Context::class);
        $context->setAspect('language', $languageAspect);
        $pageRepository = GeneralUtility::makeInstance(PageRepository::class);
        $menuRepository = GeneralUtility::makeInstance(MenuRepository::class, $context, $pageRepository, $this->createMock(EventDispatcherInterface::class));
        $page = $menuRepository->getPage(1, []);
        $pageInLanguage = $menuRepository->getPageInLanguage(1, $context, []);
        self::assertSame([], $page);
        self::assertSame([], $pageInLanguage);
    }

    /**
     * @test
     */
    public function translatedPageIsInMenuIfNavHideAndIgnoreNavHideIsSet(): void
    {
        $this->importCSVDataSet(ORIGINAL_ROOT . 'typo3conf/ext/menus/Tests/Functional/Domain/Repository/Fixtures/translated_page_with_nav_hide.csv');
        $languageAspect = GeneralUtility::makeInstance(LanguageAspect::class, 1);
        $context = GeneralUtility::makeInstance(Context::class);
        $context->setAspect('language', $languageAspect);
        $pageRepository = GeneralUtility::makeInstance(PageRepository::class);
        $menuRepository = GeneralUtility::makeInstance(MenuRepository::class, $context, $pageRepository, $this->createMock(EventDispatcherInterface::class));
        $page = $menuRepository->getPage(1, ['includeNotInMenu' => 1]);
        $pageInLanguage = $menuRepository->getPageInLanguage(1, $context, ['includeNotInMenu' => 1]);
        $page = $this->reduceResults($page);
        $pageInLanguage = $this->reduceResults($pageInLanguage);

        $expectedPage = [
            'uid' => 1,
            'pid' => 0,
            'sys_language_uid' => 1,
            'l10n_parent' => 1,
            'nav_hide' => 1,
        ];
        self::assertSame($expectedPage, $page);
        self::assertSame($expectedPage, $pageInLanguage);
    }

    /**
     * @param array $results
     * @return array
     */
    protected function reduceResults(array $results): array
    {
        $keys = ['uid', 'pid', 'sys_language_uid', 'l10n_parent', 'nav_hide'];
        return array_intersect_key($results, array_flip($keys));
    }
}
