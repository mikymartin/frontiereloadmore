<?php
declare(strict_types=1);

namespace Polimi\Frontiereloadmore\Domain\Repository;

use GeorgRinger\News\Domain\Model\News;
use GeorgRinger\News\Domain\Repository\CategoryRepository;
use GeorgRinger\News\Domain\Repository\NewsRepository;
use TYPO3\CMS\Extbase\Persistence\QueryInterface;
use TYPO3\CMS\Extbase\Persistence\QueryResultInterface;
use TYPO3\CMS\Extbase\Persistence\Generic\Typo3QuerySettings;
use TYPO3\CMS\Core\Utility\GeneralUtility;

final class RelatedNewsRepository extends NewsRepository
{
    private readonly CategoryRepository $categoryRepository;

    public function __construct(CategoryRepository $categoryRepository)
    {
        parent::__construct();
        $this->categoryRepository = $categoryRepository;
        // Extbase infers RelatedNews from this repository's name, but the records
        // are georgringer/news News objects. Keep its original persistence map.
        $this->objectType = News::class;
    }

    /** Keeps the category and date rules of newsenhanced; uses stable ordering for pagination. */
    public function findRelatedQuery(News $news, array $parentCategoryUids): ?QueryResultInterface
    {
        $valid = [];
        foreach ($parentCategoryUids as $uid) {
            $parent = $this->categoryRepository->findByUid($uid);
            if ($parent !== null) {
                $valid = array_merge($valid, $this->getAllChildCategoryUids($parent));
            }
        }
        $valid = array_unique($valid);
        $matched = [];
        foreach ($news->getCategories() as $category) {
            if (in_array($category->getUid(), $valid, true)) {
                $matched[] = $category->getUid();
            }
        }
        if ($matched === []) {
            return null;
        }

        $query = $this->createQuery();
        $querySettings = GeneralUtility::makeInstance(Typo3QuerySettings::class);
        $querySettings->setRespectStoragePage(false);
        $query->setQuerySettings($querySettings);
        $query->matching($query->logicalAnd(
            $query->in('categories.uid', $matched),
            $query->logicalNot($query->equals('uid', $news->getUid())),
            $query->greaterThanOrEqual('datetime', (new \DateTimeImmutable('-2 years'))->getTimestamp())
        ));
        $query->setOrderings([
            'datetime' => QueryInterface::ORDER_DESCENDING,
            'uid' => QueryInterface::ORDER_DESCENDING,
        ]);
        return $query->execute();
    }

    /** Keep the latest-news selection from the detail page without inheriting another repository. */
    public function findLatest(News $news, int $limit = 5): array
    {
        if ($limit <= 0) {
            return [];
        }

        $query = $this->createQuery();
        $querySettings = GeneralUtility::makeInstance(Typo3QuerySettings::class);
        $querySettings->setRespectStoragePage(false);
        $query->setQuerySettings($querySettings);
        $query->matching($query->logicalNot($query->equals('uid', $news->getUid())));
        $query->setOrderings([
            'datetime' => QueryInterface::ORDER_DESCENDING,
            'uid' => QueryInterface::ORDER_DESCENDING,
        ]);
        $query->setLimit($limit * 3);

        $currentCategoryUids = [];
        foreach ($news->getCategories() as $category) {
            $currentCategoryUids[] = $category->getUid();
        }
        $latest = [];
        foreach ($query->execute() as $item) {
            $itemCategoryUids = [];
            foreach ($item->getCategories() as $category) {
                $itemCategoryUids[] = $category->getUid();
            }
            if (array_intersect($itemCategoryUids, $currentCategoryUids) === []) {
                $latest[] = $item;
            }
            if (count($latest) >= $limit) {
                break;
            }
        }
        return $latest;
    }

    private function getAllChildCategoryUids(\GeorgRinger\News\Domain\Model\Category $category): array
    {
        $uids = [];
        foreach ($this->categoryRepository->findByParent($category) as $child) {
            $uids[] = $child->getUid();
            $uids = array_merge($uids, $this->getAllChildCategoryUids($child));
        }
        return $uids;
    }
}
