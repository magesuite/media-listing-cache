<?php

declare(strict_types=1);

namespace MageSuite\MediaListingCache\Test\Integration\Model\Cache;

class CacheKeyGeneratorTest extends \PHPUnit\Framework\TestCase
{
    protected ?\Magento\Framework\Api\Search\SearchCriteriaBuilder $searchCriteriaBuilder;
    protected ?\MageSuite\MediaListingCache\Model\Cache\CacheKeyGenerator $cacheKeyGenerator;
    protected ?\Magento\Framework\Api\FilterBuilder $filterBuilder;
    protected ?\Magento\Framework\Api\Search\FilterGroupBuilder $filterGroupBuilder;

    protected function setUp(): void
    {
        $objectManager = \Magento\TestFramework\Helper\Bootstrap::getObjectManager();
        $this->searchCriteriaBuilder = $objectManager->get(\Magento\Framework\Api\Search\SearchCriteriaBuilder::class);
        $this->cacheKeyGenerator = $objectManager->get(\MageSuite\MediaListingCache\Model\Cache\CacheKeyGenerator::class);
        $this->filterBuilder = $objectManager->get(\Magento\Framework\Api\FilterBuilder::class);
        $this->filterGroupBuilder = $objectManager->get(\Magento\Framework\Api\Search\FilterGroupBuilder::class);
    }

    public function testItGeneratesProperCacheKey(): void
    {
        $filterEntityId = $this->filterBuilder->setField('entity_id')
            ->setValue(1)
            ->setConditionType('eq')
            ->create();

        $filterStoreId = $this->filterBuilder->setField('store_id')
            ->setValue(1)
            ->setConditionType('eq')
            ->create();

        $criteriaWithSeparateFilters = $this->searchCriteriaBuilder
            ->addFilter($filterEntityId)
            ->addFilter($filterStoreId)
            ->addSortOrder('entity_id', 'ASC')
            ->setPageSize(1)
            ->setCurrentPage(1)
            ->create();
        $criteriaWithSeparateFilters->setRequestName('some_request_name');

        $separateFiltersCacheKey = $this->cacheKeyGenerator->generate($criteriaWithSeparateFilters);
        $this->assertEquals('8f420e287d5c404f814d792705ccb093', $separateFiltersCacheKey);

        $criteriaWithFilterGroup = $this->searchCriteriaBuilder
            ->addSortOrder('entity_id', 'ASC')
            ->setPageSize(1)
            ->setCurrentPage(1)
            ->create();

        $criteriaWithFilterGroup->setRequestName('some_request_name')
            ->setFilterGroups([
            $this->filterGroupBuilder
                ->addFilter($filterEntityId)
                ->addFilter($filterStoreId)
                ->create()
        ]);

        $filterGroupsCacheKey = $this->cacheKeyGenerator->generate($criteriaWithFilterGroup);
        $this->assertEquals('526488982612a8271fede8523fc43c20', $filterGroupsCacheKey);

        $emptyCriteria = $this->searchCriteriaBuilder->create();
        $emptyCriteriaCacheKey = $this->cacheKeyGenerator->generate($emptyCriteria);

        $this->assertEquals('1311a05d2a654809d803decd3e982805', $emptyCriteriaCacheKey);

        $this->assertCount(3, array_unique([
            $separateFiltersCacheKey,
            $filterGroupsCacheKey,
            $emptyCriteriaCacheKey
        ]));
    }

    /**
     * @dataProvider paginationDataProvider
     */
    public function testItGeneratesProperCacheKeyOnPageChange(int $currentPage, int $pageSize, string $expectedKey): void
    {
        $filterEntityId = $this->filterBuilder->setField('entity_id')
            ->setValue(1)
            ->setConditionType('eq')
            ->create();

        $criteria = $this->searchCriteriaBuilder
            ->addFilter($filterEntityId)
            ->addSortOrder('entity_id', 'ASC')
            ->setPageSize($pageSize)
            ->setCurrentPage($currentPage)
            ->create();
        $criteria->setRequestName('some_request_name');

        $cacheKey = $this->cacheKeyGenerator->generate($criteria);

        $this->assertEquals($expectedKey, $cacheKey);
    }

    protected function paginationDataProvider(): array
    {
        return [
            'Page 1, Limit 10' => [1, 10, 'f5878ccba90680eba8ab184348deb723'],
            'Page 2, Limit 10' => [2, 10, 'c23c1a0628eda667f5f11787c40fcd1f'],
            'Page 3, Limit 10' => [3, 10, '45e077644f050f8b4a9707cb6fee8e21'],
            'Page 1, Limit 15' => [1, 15, '70a2f25b6509c97a22889097a4f389a9'],
            'Page 1, Limit 20' => [1, 20, '89da660f262fe00c5c1de8c4f0abf202'],
            'Page 2, Limit 40' => [2, 40, '634cf97df7ce522d6c38a19de398008f']
        ];
    }
}
