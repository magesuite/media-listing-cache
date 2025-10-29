<?php

namespace MageSuite\MediaListingCache\Model\Cache\Type;

class MediaListing extends \Magento\Framework\Cache\Frontend\Decorator\TagScope
{
    public const TYPE_IDENTIFIER = 'media_listing';
    public const MEDIA_LISTING_CACHE_TAG = 'MEDIA_LISTING_CACHE';
    public const DIRECTORY_TREE_CACHE_TAG = 'DIRECTORY_TREE_CACHE';
    public const DIRECTORY_TREE_CACHE_KEY = 'new_media_gallery_directory_tree';
    public const DIRECTORY_TREE_CACHE_LIFETIME = 1209600; // 14 days in seconds

    /**
     * @param \Magento\Framework\App\Cache\Type\FrontendPool $cacheFrontendPool
     */
    public function __construct(
        \Magento\Framework\App\Cache\Type\FrontendPool $cacheFrontendPool,
        protected \Magento\Framework\Serialize\SerializerInterface $serializer,
        protected \Magento\MediaGalleryUi\Model\Directories\GetDirectoryTree $directoryTreeGenerator,
    ) {
        parent::__construct($cacheFrontendPool->get(self::TYPE_IDENTIFIER), self::TYPE_IDENTIFIER);
    }

    public function getDirectoryTree(): ?array
    {
        $data = $this->load(static::DIRECTORY_TREE_CACHE_KEY);
        return $data ? $this->serializer->unserialize($data) : null;
    }

    public function saveDirectoryTree(array $data): void
    {
        $this->save(
            $this->serializer->serialize($data),
            static::DIRECTORY_TREE_CACHE_KEY,
            [self::DIRECTORY_TREE_CACHE_TAG],
            static::DIRECTORY_TREE_CACHE_LIFETIME
        );
    }

    public function addDirectoryToTreeCache(string $path): void
    {
        $pathParts = explode('/', trim($path, '/'));
        $pathWithoutNewDirectoryName = implode('/', array_slice($pathParts, 0, -1));
        $directoryTree = $this->getDirectoryTree() ?? $this->directoryTreeGenerator->execute();
        $directoryAsArray = $this->getDirectoryAsArray($path);
        $this->addDirectoryArrayToParent($directoryAsArray, $pathWithoutNewDirectoryName, $directoryTree);
        $this->saveDirectoryTree($directoryTree);
    }

    protected function addDirectoryArrayToParent(array $directory, string $parentPath, array &$tree): bool
    {
        foreach ($tree as &$node) {
            if ($node['path'] === $parentPath) {
                $node['children'][] = $directory;
                return true;
            }

            if (!empty($node['children'])) {
                $success = $this->addDirectoryArrayToParent($directory, $parentPath, $node['children']);

                if ($success) {
                    return true;
                }
            }
        }

        return false;
    }

    protected function getDirectoryAsArray(string $path): array
    {
        return [
            'text' => basename($path),
            'id' => $path,
            'li_attr' => ['data-id' => $path],
            'path' => $path,
            'path_array' => explode('/', trim($path, '/')),
            'children' => [],
        ];
    }

    public function removeDirectoryFromDirectoryTree(string $path): void
    {
        $directoryTree = $this->getDirectoryTree() ?? $this->directoryTreeGenerator->execute();
        $this->removeDirectoryFromParent($path, $directoryTree);
        $this->saveDirectoryTree($directoryTree);
    }

    protected function removeDirectoryFromParent(string $path, array &$directoryTree): void
    {
        foreach ($directoryTree as $key => &$node) {
            if ($node['path'] === $path) {
                unset($directoryTree[$key]);
                return;
            }

            if (!empty($node['children'])) {
                $this->removeDirectoryFromParent($path, $node['children']);
            }
        }
    }

    public function getFileListing(string $cacheKey): ?array
    {
        $data = $this->load($cacheKey);
        return $data ? $this->serializer->unserialize($data) : null;
    }

    public function saveFileListing(string $cacheKey, array $data): void
    {
        $this->save($this->serializer->serialize($data), $cacheKey, [self::MEDIA_LISTING_CACHE_TAG], 86400);
    }
}
