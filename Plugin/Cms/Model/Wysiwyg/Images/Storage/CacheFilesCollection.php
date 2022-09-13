<?php

namespace MageSuite\MediaListingCache\Plugin\Cms\Model\Wysiwyg\Images\Storage;

class CacheFilesCollection
{
    const ONE_DAY = 86400;

    const FILES_COLLECTION_TAG = 'files_collection';

    protected \MageSuite\MediaListingCache\Model\Cache\Type\MediaListing $cache;

    protected \Magento\Framework\Data\CollectionFactory $collectionFactory;

    protected \Magento\Framework\Serialize\SerializerInterface $serializer;

    protected \Magento\Framework\DataObjectFactory $dataObjectFactory;

    protected \Magento\Framework\Event\ManagerInterface $eventManager;

    public function __construct(
        \MageSuite\MediaListingCache\Model\Cache\Type\MediaListing $cache,
        \Magento\Framework\Data\CollectionFactory $collectionFactory,
        \Magento\Framework\Serialize\SerializerInterface $serializer,
        \Magento\Framework\DataObjectFactory $dataObjectFactory,
        \Magento\Framework\Event\ManagerInterface $eventManager
    ) {
        $this->cache = $cache;
        $this->collectionFactory = $collectionFactory;
        $this->serializer = $serializer;
        $this->dataObjectFactory = $dataObjectFactory;
        $this->eventManager = $eventManager;
    }

    public function aroundGetFilesCollection(\Magento\Cms\Model\Wysiwyg\Images\Storage $subject, callable $proceed, $path, $type = null)
    {
        $cacheKey = hash('md5', $path . $type);
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
        $this->eventManager->dispatch('media_gallery_upload');

        return $result;
    }

    public function afterDeleteFile(\Magento\Cms\Model\Wysiwyg\Images\Storage $subject, $result)
    {
        $this->eventManager->dispatch('media_gallery_upload');

        return $result;
    }

    public function afterCreateDirectory(\Magento\Cms\Model\Wysiwyg\Images\Storage $subject, $result)
    {
        $this->cleanImagesTreeCache();

        return $result;
    }

    public function afterDeleteDirectory(\Magento\Cms\Model\Wysiwyg\Images\Storage $subject, $result)
    {
        $this->cleanImagesTreeCache();

        return $result;
    }

    protected function cleanImagesTreeCache(): void
    {
        $tags = [\MageSuite\MediaListingCache\Plugin\Cms\Block\Adminhtml\Wysiwyg\Images\Tree\CacheTreeJson::TREE_JSON_TAG];
        $this->cache->clean(\Zend_Cache::CLEANING_MODE_MATCHING_TAG, $tags);
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
        // phpcs:disable Standard.Classes.RequireFullPath
        $items = array_map(function ($item) use ($serializer) {
            return $serializer->serialize($item->getData());
        }, $collection->getItems());

        return $serializer->serialize($items);
    }
}
