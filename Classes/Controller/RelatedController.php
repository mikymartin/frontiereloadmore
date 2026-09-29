<?php
declare(strict_types=1);

namespace Polimi\Frontiereloadmore\Controller;

use Polimi\Frontiereloadmore\Domain\Repository\RelatedNewsRepository;
use TYPO3\CMS\Core\Pagination\SimplePagination;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Mvc\Controller\ActionController;
use TYPO3\CMS\Extbase\Pagination\QueryResultPaginator;
use Psr\Http\Message\ResponseInterface;

final class RelatedController extends ActionController
{
    public function listAction(): ResponseInterface
    {
        $contentObject = $this->request->getAttribute('currentContentObject');
        $newsUid = (int)($contentObject?->data['news'] ?? 0);
        $newsRepository = GeneralUtility::makeInstance(RelatedNewsRepository::class);
        $news = $newsUid > 0 ? $newsRepository->findByUid($newsUid) : null;
        $size = max(1, min(100, (int)($this->settings['itemsPerPage'] ?? 9)));
        $page = max(1, (int)($this->request->hasArgument('relatedPage') ? $this->request->getArgument('relatedPage') : 1));
        $parentIds = GeneralUtility::intExplode(',', (string)($this->settings['idsParentCategoryRelatedNews'] ?? ''), true);
        $results = $news ? $newsRepository->findRelatedQuery($news, $parentIds) : null;
        $relatedTotal = $results ? count($results) : 0;
        $paginator = $results ? GeneralUtility::makeInstance(QueryResultPaginator::class, $results, $page, $size) : null;
        $pagination = $paginator ? GeneralUtility::makeInstance(SimplePagination::class, $paginator) : null;

        $latest = [];
        if ($news !== null) {
            $latest = $newsRepository->findLatest($news, max(0, (int)($this->settings['maxNumberLatestNews'] ?? 5)));
        }
        $this->view->assignMultiple([
            'relatedNews' => $paginator?->getPaginatedItems() ?? [],
            'relatedPaginator' => $paginator,
            'relatedPagination' => $pagination,
            'relatedTotal' => $relatedTotal,
            'latestNews' => $latest,
            'newsUid' => $newsUid,
            'settings' => $this->settings,
        ]);
        return $this->htmlResponse();
    }
}
