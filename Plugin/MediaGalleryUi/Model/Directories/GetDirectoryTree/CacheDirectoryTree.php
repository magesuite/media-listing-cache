<?php

declare(strict_types=1);

namespace MageSuite\MediaListingCache\Plugin\MediaGalleryUi\Model\Directories\GetDirectoryTree;

class CacheDirectoryTree
{
    const TYPE_IDENTIFIER = 'new_media_gallery_directory_tree';
    const TWO_WEEKS_IN_SECONDS = 1209600;

    public function __construct(
        protected \MageSuite\MediaListingCache\Model\Cache\Type\MediaListing $cache,
        protected \Magento\Framework\Serialize\SerializerInterface $serializer
    ) {}

    public function aroundExecute(\Magento\MediaGalleryUi\Model\Directories\GetDirectoryTree $subject, callable $proceed): array
    {
        $identifier = self::TYPE_IDENTIFIER;
        $data = $this->cache->load($identifier);

        if (!$data) {
            $data = $proceed();
            $this->cache->save(
                $this->serializer->serialize($data),
                $identifier,
                [\MageSuite\MediaListingCache\Model\Cache\Type\MediaListing::CACHE_TAG],
                self::TWO_WEEKS_IN_SECONDS
            );
        } else {
            $data = $this->serializer->unserialize($data);
        }

        return $data;
    }
}
