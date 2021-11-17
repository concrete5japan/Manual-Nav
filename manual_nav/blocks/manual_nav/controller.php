<?php

namespace Concrete\Package\ManualNav\Block\ManualNav;

use Concrete\Core\Block\BlockController;
use Concrete\Core\Database\Connection\Connection;
use Concrete\Core\File\File;
use Concrete\Core\Html\Service\FontAwesomeIcon;
use Concrete\Core\Page\Page;
use Less_Parser;
use Less_Tree_Rule;

class Controller extends BlockController
{
    protected $btTable = 'btManualNav';
    protected $btExportTables = ['btManualNav', 'btManualNavEntries'];
    protected $btInterfaceWidth = '600';
    protected $btWrapperClass = 'ccm-ui';
    protected $btInterfaceHeight = '465';
    protected $btCacheBlockRecord = true;
    protected $btExportPageColumns = ['cID', 'internalLinkCID'];
    protected $btExportFileColumns = ['fID', 'internalLinkFID'];
    protected $btCacheBlockOutput = true;
    protected $btCacheBlockOutputOnPost = true;
    protected $btCacheBlockOutputForRegisteredUsers = false;
    protected $displayImage;

    public function getBlockTypeDescription()
    {
        return t('Manual Nav.');
    }

    public function getBlockTypeName()
    {
        return t('Manual Nav');
    }

    public function registerViewAssets($outputContent = '')
    {
        if ($this->displayImage == 3) {
            $this->requireAsset('css', 'font-awesome');
        }
    }

    public function getSearchableContent()
    {
        $content = '';
        $r = $this->createQueryBuilder()
            ->select('title')
            ->from('btManualNavEntries', 'e')
            ->where('bID = :bID')
            ->setParameter('bID', $this->bID)
            ->execute();
        while ($row = $r->fetch()) {
            $content .= $row['title'] . ' ';
        }

        return $content;
    }

    public function add()
    {
        $this->requireAsset('core/file-manager');
        $this->requireAsset('core/sitemap');
        $this->requireAsset('redactor');
        $this->requireAsset('css', 'font-awesome');
        $this->loadIcons();
        $this->set('al', $this->app->make('helper/concrete/file_manager'));
    }

    public function edit()
    {
        $this->requireAsset('core/file-manager');
        $this->requireAsset('core/sitemap');
        $this->requireAsset('redactor');
        $this->requireAsset('css', 'font-awesome');
        $this->set('rows', $this->getManualNavEntries());
        $this->loadIcons();
        $this->set('al', $this->app->make('helper/concrete/file_manager'));
    }

    public function view()
    {
        $fh = $this->app->make('helper/file');
        // in view mode, linkURL takes us to where we need to go whether it's on our site or elsewhere
        $rows = [];
        foreach ($this->getManualNavEntries() as $q) {
            // Update the variable types
            $q['fID'] = (int) $q['fID'];
            $q['cID'] = (int) $q['cID'];
            $q['internalLinkCID'] = (int) $q['internalLinkCID'];
            $q['internalLinkFID'] = (int) $q['internalLinkFID'];

            if (!$q['linkURL'] && $q['internalLinkCID']) {
                $lc = Page::getByID($q['internalLinkCID'], 'ACTIVE');
                if (is_object($lc)) {
                    $q['linkURL'] = ($lc->getCollectionPointerExternalLink() != '') ? $lc->getCollectionPointerExternalLink() : $lc->getCollectionLink();
                    $q['collectionName'] = $lc->getCollectionName();
                }
            } elseif (!$q['linkURL'] && $q['internalLinkFID']) {
                $file = File::getByID($q['internalLinkFID']);
                if (is_object($file)) {
                    $q['linkURL'] = $file->getDownloadURL();
                    $q['collectionName'] = $file->getFileName();
                }
            }

            //image type
            if ($this->displayImage == 1) {
                $lc = Page::getByID($q['internalLinkCID'], 'ACTIVE');
                if (is_object($lc)) {
                    $q['image'] = $lc->getAttribute('thumbnail');
                }
            } elseif ($this->displayImage == 2) {
                $file = File::getByID($q['fID']);
                 if (is_object($file)) {
                    $q['image'] = $file;
                }
            }

            $q['isVectorImage'] = false;
            if ($this->displayImage) {
                $ex = ['svg'];
                if (is_object($q['image'])) {
                    $q['isVectorImage'] = in_array(strtolower($fh->getExtension($q['image']->getFilename())), $ex, true);
                }
            }

            $rows[] = $q;
        }
        $this->set('rows', $rows);
    }

    public function duplicate($newBID)
    {
        parent::duplicate($newBID);
        $r = $this->createQueryBuilder()
            ->select('fID', 'icon', 'linkURL', 'title', 'sortOrder', 'internalLinkCID', 'internalLinkFID', 'openInNewWindow')
            ->from('btManualNavEntries')
            ->where('bID = :bID')
            ->setParameter('bID', $this->bID)
            ->execute();
        while ($row = $r->fetch()) {
            $this->createQueryBuilder()
                ->insert('btManualNavEntries')
                ->setValue('bID', ':bID')
                ->setValue('fID', ':fID')
                ->setValue('icon', ':icon')
                ->setValue('linkURL', ':linkURL')
                ->setValue('title', ':title')
                ->setValue('sortOrder', ':sortOrder')
                ->setValue('internalLinkCID', ':internalLinkCID')
                ->setValue('internalLinkFID', ':internalLinkFID')
                ->setValue('openInNewWindow', ':openInNewWindow')
                ->setParameter('bID', $newBID)
                ->setParameter('fID', $row['fID'])
                ->setParameter('icon', $row['icon'])
                ->setParameter('linkURL', $row['linkURL'])
                ->setParameter('title', $row['title'])
                ->setParameter('sortOrder', $row['sortOrder'])
                ->setParameter('internalLinkCID', $row['internalLinkCID'])
                ->setParameter('internalLinkFID', $row['internalLinkFID'])
                ->setParameter('openInNewWindow', $row['openInNewWindow'])
                ->execute();
        }
    }

    public function delete()
    {
        $this->deleteManualNavEntries();
        parent::delete();
    }

    public function save($args)
    {
        $this->deleteManualNavEntries();
        $count = count($args['sortOrder']);
        $i = 0;
        parent::save($args);
        while ($i < $count) {
            $linkURL = $args['linkURL'][$i];
            $internalLinkCID = (int) $args['internalLinkCID'][$i];
            $internalLinkFID = (int) $args['internalLinkFID'][$i];
            switch ((int) $args['linkType'][$i]) {
                case 1:
                    $linkURL = '';
                    break;
                case 2:
                    $internalLinkCID = 0;
                    break;
                default:
                    $linkURL = '';
                    $internalLinkCID = 0;
                    break;
            }
            if ($args['fID'][$i] == null) {
                $args['fID'][$i] = 0;
            }

            $openInNewWindow = $args['openInNewWindow'][$i] == 1 ? 1 : 0;

            $this->createQueryBuilder()
                ->insert('btManualNavEntries')
                ->setValue('bID', ':bID')
                ->setValue('fID', ':fID')
                ->setValue('icon', ':icon')
                ->setValue('linkURL', ':linkURL')
                ->setValue('title', ':title')
                ->setValue('sortOrder', ':sortOrder')
                ->setValue('internalLinkCID', ':internalLinkCID')
                ->setValue('internalLinkFID', ':internalLinkFID')
                ->setValue('openInNewWindow', ':openInNewWindow')
                ->setParameter('bID', $this->bID)
                ->setParameter('fID', (int) $args['fID'][$i])
                ->setParameter('icon', $args['icon'][$i])
                ->setParameter('linkURL', $linkURL)
                ->setParameter('title', $args['title'][$i])
                ->setParameter('sortOrder', (int) $args['sortOrder'][$i])
                ->setParameter('internalLinkCID', $internalLinkCID)
                ->setParameter('internalLinkFID', $internalLinkFID)
                ->setParameter('openInNewWindow', $openInNewWindow)
                ->execute();

            ++$i;
        }
    }

    protected function loadIcons()
    {
        // Set font awesome icons
        $classes = $this->getIconClasses();
        $icons = ['' => t('Choose Icon')];
        $txt = $this->app->make('helper/text');
        foreach ($classes as $class) {
            if (is_array($class)) {
                $icons[$class['class']] = $class['name'];
            } else {
                $icons[$class] = $txt->unhandle($class);
            }
        }
        $this->set('icons', $icons);
    }

    /**
     * @throws \Doctrine\DBAL\Exception
     * @throws \Illuminate\Contracts\Container\BindingResolutionException
     *
     * @return array
     */
    protected function getManualNavEntries()
    {
        return $this->createQueryBuilder()
            ->select('fID', 'icon', 'cID', 'linkURL', 'internalLinkCID', 'internalLinkFID', 'title', 'sortOrder', 'openInNewWindow')
            ->from('btManualNavEntries', 'e')
            ->where('bID = :bID')
            ->orderBy('sortOrder')
            ->setParameter('bID', $this->bID)
            ->execute()
            ->fetchAll();
    }

    /**
     * @throws \Less_Exception_Parser
     *
     * @return array
     */
    protected function getIconClasses()
    {
        $icons = [];

        if (class_exists(FontAwesomeIcon::class)) {
            // V9
            $txt = $this->app->make('helper/text');
            $webfonts = [
                [
                    'prefix' => 'far',
                    'handle' => 'fa-regular-400',
                    'category' => 'Regular',
                ],
                [
                    'prefix' => 'fas',
                    'handle' => 'fa-solid-900',
                    'category' => 'Solid',
                ],
                [
                    'prefix' => 'fab',
                    'handle' => 'fa-brands-400',
                    'category' => 'Brands',
                ],
            ];
            foreach ($webfonts as $webfont) {
                $webfontsvg = DIR_BASE_CORE . '/css/webfonts/' . $webfont['handle'] . '.svg';
                if (file_exists($webfontsvg)) {
                    $xml = simplexml_load_file($webfontsvg);
                    foreach ($xml->defs->font->glyph as $glyph) {
                        $icons[] = [
                            'class' => $webfont['prefix'] . ' fa-' . $glyph['glyph-name'],
                            'name' => $webfont['category'] . ' ' . $txt->unhandle($glyph['glyph-name'])
                        ];
                    }
                }
            }
        } else {
            // V8
            $iconLessFile = DIR_BASE_CORE . '/css/build/vendor/font-awesome/variables.less';
            if (file_exists($iconLessFile)) {
                $l = new Less_Parser();
                $parser = $l->parseFile($iconLessFile, false, true);
                $rules = $parser->rules;

                foreach ($rules as $rule) {
                    if (($rule instanceof Less_Tree_Rule) && strpos($rule->name, '@fa-var') === 0) {
                        $name = str_replace('@fa-var-', '', $rule->name);
                        $icons[] = $name;
                    }
                }
                asort($icons);
            }
        }

        return $icons;
    }

    protected function deleteManualNavEntries()
    {
        $this->createQueryBuilder()
            ->delete('btManualNavEntries')
            ->where('bID = :bID')
            ->setParameter('bID', $this->bID)
            ->execute();
    }

    /**
     * @throws \Illuminate\Contracts\Container\BindingResolutionException
     *
     * @return \Doctrine\DBAL\Query\QueryBuilder
     */
    protected function createQueryBuilder()
    {
        /** @var Connection $db */
        $db = $this->app->make('database')->connection();

        return $db->createQueryBuilder();
    }
}
