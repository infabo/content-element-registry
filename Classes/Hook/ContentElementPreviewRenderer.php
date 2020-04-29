<?php

declare(strict_types=1);

namespace Digitalwerk\ContentElementRegistry\Hook;

use Digitalwerk\ContentElementRegistry\ContentElement\AbstractContentElementRegistryItem;
use Digitalwerk\ContentElementRegistry\Core\ContentElementRegistry;
use Digitalwerk\ContentElementRegistry\Domain\Model\ContentElement;
use TYPO3\CMS\Backend\Utility\BackendUtility;
use TYPO3\CMS\Backend\View\PageLayoutView;
use TYPO3\CMS\Backend\View\PageLayoutViewDrawItemHookInterface;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Object\ObjectManager;
use TYPO3\CMS\Extbase\Persistence\Generic\Mapper\DataMapper;
use TYPO3\CMS\Fluid\View\StandaloneView;

/**
 * Class ContentElementPreviewRenderer
 */
class ContentElementPreviewRenderer implements PageLayoutViewDrawItemHookInterface
{
    /**
     * Preprocesses the preview rendering of a content element.
     *
     * @param \TYPO3\CMS\Backend\View\PageLayoutView $parentObject Calling parent object
     * @param bool $drawItem Whether to draw the item using the default functionalities
     * @param string $headerContent Header content
     * @param string $itemContent Item content
     * @param array $row Record row of tt_content
     */
    public function preProcess(
        \TYPO3\CMS\Backend\View\PageLayoutView &$parentObject,
        &$drawItem,
        &$headerContent,
        &$itemContent,
        array &$row
    ) {
        $contentElementRegistry = ContentElementRegistry::getInstance();
        if ($contentElementRegistry->existsContentElement($row['CType'])) {
            /** @var AbstractContentElementRegistryItem $contentElement */
            $contentElement = $contentElementRegistry->getContentElement($row['CType']);
            $drawItem = false;
            $view = GeneralUtility::makeInstance(StandaloneView::class);
            $view->setPartialRootPaths(["EXT:{$contentElement->getExtensionKey()}/Resources/Private/Partials"]);
            $view->setTemplatePathAndFilename("EXT:{$contentElement->getExtensionKey()}/Resources/Private/Templates/ContentElements/{$contentElement->getTemplateName()}.html");

            $itemContent = $this->getEditContentLink($parentObject, $row);
            $itemContent .= $view->renderSection(
                'Preview',
                [
                    'data' => $row,
                    'contentElement' => $this->getDataMapper()->map(
                        ContentElement::class,
                        [$row]
                    )[0],
                ],
                true
            );
        }
    }

    /**
     * @param PageLayoutView $pageLayoutView
     * @param array $row
     *
     * @return string
     */
    private function getEditContentLink(PageLayoutView $pageLayoutView, array $row)
    {
        return "<strong>
                    {$pageLayoutView->linkEditContent(
                        $GLOBALS['LANG']->sL(
                            BackendUtility::getLabelFromItemListMerged($row['pid'], 'tt_content', 'CType', $row['CType'])
                        ),
                        $row
                    )}
                </strong>";
    }

    /**
     * @return DataMapper
     */
    protected function getDataMapper()
    {
        return GeneralUtility::makeInstance(ObjectManager::class)->get(DataMapper::class);
    }
}
