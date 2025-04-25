<?php

declare(strict_types=1);

namespace MageSuite\MediaListingCache\Model\Cache;

class CacheKeyGenerator
{
    public function __construct(
        protected \Magento\Framework\Serialize\SerializerInterface $serializer
    ) {
    }

    public function generate(\Magento\Framework\Api\Search\SearchCriteriaInterface $searchCriteria): string
    {
        $cacheParts = [
            $searchCriteria->getRequestName(),
            $searchCriteria->getCurrentPage(),
            $searchCriteria->getPageSize(),
            $this->getFiltersData($searchCriteria),
            $this->getSortOrderData($searchCriteria),
        ];

        return hash('md5', $this->serializer->serialize($cacheParts));
    }

    protected function getFiltersData(\Magento\Framework\Api\Search\SearchCriteriaInterface $searchCriteria): array
    {
        $filters = [];
        foreach ($searchCriteria->getFilterGroups() as $groupIndex => $filterGroup) {
            $filters[$groupIndex] = [];
            foreach ($filterGroup->getFilters() as $filter) {
                $filters[$groupIndex][] = [
                    'field' => $filter->getField(),
                    'value' => $filter->getValue(),
                    'condition_type' => $filter->getConditionType(),
                ];
            }
        }

        return $filters;
    }

    protected function getSortOrderData(\Magento\Framework\Api\Search\SearchCriteriaInterface $searchCriteria): array
    {
        $sortOrders = [] ;
        foreach ($searchCriteria->getSortOrders() as $sortOrder) {
            $sortOrders[] = [
                'field' => $sortOrder->getField(),
                'direction' => $sortOrder->getDirection(),
            ];
        }

        return $sortOrders;
    }
}
