<?php

namespace MageSuite\MediaListingCache\Plugin\Cms\Model\Wysiwyg\Images\Storage;

class CacheFilesCollection
{
    /**
     * @var \MageSuite\MediaListingCache\Model\Cache\Type\MediaListing
     */
    protected $cache;

    const ONE_DAY = 86400;

    const FILES_COLLECTION_TAG = 'files_collection';

    /**
     * @var \Magento\Framework\Data\CollectionFactory
     */
    protected $collectionFactory;

    /**
     * @var \Magento\Framework\Serialize\SerializerInterface
     */
    protected $serializer;

    /**
     * @var \Magento\Framework\DataObjectFactory
     */
    protected $dataObjectFactory;

    public function __construct(
        \MageSuite\MediaListingCache\Model\Cache\Type\MediaListing $cache,
        \Magento\Framework\Data\CollectionFactory $collectionFactory,
        \Magento\Framework\Serialize\SerializerInterface $serializer,
        \Magento\Framework\DataObjectFactory $dataObjectFactory
    ) {
        $this->cache = $cache;
        $this->collectionFactory = $collectionFactory;
        $this->serializer = $serializer;
        $this->dataObjectFactory = $dataObjectFactory;
    }

    public function aroundGetFilesCollection(\Magento\Cms\Model\Wysiwyg\Images\Storage $subject, callable $proceed, $path, $type = null)
    {
        $cacheKey = md5($path . $type);

        $collectionItemsData = $this->cache->load($cacheKey);

        if ($collectionItemsData == null) {
            $collection = $proceed($path, $type);

            $this->cache->save(
                $this->serializeCollectionItemsData($collection),
                $cacheKey,
                [self::FILES_COLLECTION_TAG],
                self::ONE_DAY
            );
        } else {
            $collection = $this->unserializeCollectionItemsData($collectionItemsData);
        }

        return $collection;
    }

    public function afterUploadFile(\Magento\Cms\Model\Wysiwyg\Images\Storage $subject, $result)
    {
        $this->cache->clean(\Zend_Cache::CLEANING_MODE_MATCHING_TAG, [self::FILES_COLLECTION_TAG]);

        return $result;
    }

    public function afterDeleteFile(\Magento\Cms\Model\Wysiwyg\Images\Storage $subject, $result)
    {
        $this->cache->clean(\Zend_Cache::CLEANING_MODE_MATCHING_TAG, [self::FILES_COLLECTION_TAG]);

        return $result;
    }

    public function afterCreateDirectory(\Magento\Cms\Model\Wysiwyg\Images\Storage $subject, $result)
    {
        $tags = [\MageSuite\MediaListingCache\Plugin\Cms\Block\Adminhtml\Wysiwyg\Images\Tree\CacheTreeJson::TREE_JSON_TAG];

        $this->cache->clean(\Zend_Cache::CLEANING_MODE_MATCHING_TAG, $tags);

        return $result;
    }

    public function afterDeleteDirectory(\Magento\Cms\Model\Wysiwyg\Images\Storage $subject, $result)
    {
        $tags = [\MageSuite\MediaListingCache\Plugin\Cms\Block\Adminhtml\Wysiwyg\Images\Tree\CacheTreeJson::TREE_JSON_TAG];

        $this->cache->clean(\Zend_Cache::CLEANING_MODE_MATCHING_TAG, $tags);

        return $result;
    }

    protected function unserializeCollectionItemsData($serializedData)
    {
        /** @var \Magento\Framework\Data\Collection $collection */
        $collection = $this->collectionFactory->create();
        $itemsData = $this->serializer->unserialize($serializedData);

        foreach ($itemsData as $value) {
            $itemDataObject = $this->dataObjectFactory->create();
            $itemDataObject->addData($this->serializer->unserialize($value));
            $collection->addItem($itemDataObject);
        }

        return $collection;
    }

    protected function serializeCollectionItemsData($collection)
    {
        $serializer = $this->serializer;
        $items = array_map(function ($item) use ($serializer){
            return $serializer->serialize($item->getData());
        }, $collection->getItems());

        return $serializer->serialize($items);
    }
}
