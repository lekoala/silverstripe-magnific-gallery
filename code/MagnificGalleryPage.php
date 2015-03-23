<?php

class MagnificGalleryPage extends Page
{
    private static $description = 'A magnific image gallery';
    private static $icon        = 'magnific-gallery/images/image-gallery-icon.png';
    private static $db          = array(
        'AlbumEffect' => 'Varchar',
        'MediaPerPage' => 'Int',
    );
    private static $has_one     = array(
        'RootFolder' => 'Folder'
    );
    private static $defaults    = array(
        'AlbumEffect' => 'lily',
        'MediaPerPage' => '30',
    );
    private static $has_many    = array(
        'Albums' => 'MagnificGalleryAlbum',
        'GalleryItems' => 'MagnifcGalleryItem'
    );
    private static $translate   = array(
        'Title', 'Content', 'URLSegment', 'MenuTitle', 'MetaDescription'
    );

    /**
     * List all available effects
     * @link http://tympanus.net/Development/HoverEffectIdeas/
     * @return array
     */
    public static function listEffects()
    {
        return array('lily', 'sadie', 'honey', 'layla', 'zoe', 'oscar', 'marley',
            'ruby', 'roxy', 'bubba', 'romeo', 'dexter', 'sarah', 'chico', 'milo',
            'julia', 'goliath', 'hera', 'winston', 'selena', 'terry', 'phoebe', 'apollo',
            'kira', 'steve', 'moses', 'jazz', 'ming', 'lexi', 'duke');
    }

    function onBeforeWrite()
    {
        parent::onBeforeWrite();
        $this->checkFolder();
    }

    function onBeforeDelete()
    {
        // check if Page still exists in live mode
        $className = $this->ClassName;
        $livePage  = Versioned::get_one_by_stage($className, "Live",
                "\"{$className}_Live\".\"ID\" = {$this->ID}");
        // check if Page still exists in stage mode
        $stagePage = Versioned::get_one_by_stage($className, "Stage",
                "\"{$className}\".\"ID\" = {$this->ID}");

        // if Page only exists in Live OR Stage mode -> Page will be deleted completely
        if (!($livePage && $stagePage)) {
            // delete existing Albums
            $this->Albums()->removeAll();

            // remove folder`
            if ($this->RootFolderID && $this->RootFolder()->ID) {
                $this->RootFolder()->delete();
            }
        }

        parent::onBeforeDelete();
    }

    function checkFolder()
    {
        if (!$this->exists()) {
            return;
        }
        if (!$this->URLSegment) {
            return;
        }
        $baseFolder = '';
        if (class_exists('Subsite') && self::config()->use_subsite_integration) {
            if ($this->SubsiteID) {
                $subsite = $this->Subsite();
                if ($subsite->hasField('BaseFolder')) {
                    $baseFolder = $subsite->BaseFolder;
                } else {
                    $filter     = new FileNameFilter();
                    $baseFolder = $filter->filter($subsite->getTitle());
                    $baseFolder = str_replace(' ', '',
                        ucwords(str_replace('-', ' ', $baseFolder)));
                }
                $baseFolder .= '/';
            }
        }
        $folderPath = $baseFolder."galleries/{$this->URLSegment}";
        $folder     = Folder::find_or_make($folderPath);
        if ($this->RootFolderID && $folder->ID != $this->RootFolderID) {
            if ($this->RootFolder()->exists()) {
                // We need to rename current folder
                $this->RootFolder()->setFilename($folder->Filename);
                $this->RootFolder()->write();
                $folder->deleteDatabaseOnly(); //Otherwise we keep a stupid clone that will be used as the parent
            } else {
                 $this->RootFolderID = $folder->ID;
            }
        } else {
            $this->RootFolderID = $folder->ID;
        }
    }

    public function getCMSFields()
    {
        $fields = parent::getCMSFields();

        // Build albums tab
        $fields->addFieldToTab('Root', $albumTab   = new Tab('Albums'), 'Main');
        $albumTab->setTitle(_t('MagnificGalleryPage.ALBUMS', 'Albums'));
        if ($rootFolder = $this->RootFolder()) {
            $albumConfig = GridFieldConfig_RecordEditor::create();
            // Enable bulk image loading if necessary module is installed
            // @see composer.json/suggests
            if (class_exists('GridFieldBulkManager')) {
                $albumConfig->addComponent(new GridFieldBulkManager());
            }
            // Enable album sorting if necessary module is installed
            // @see composer.json/suggests
            if (class_exists('GridFieldSortableRows')) {
                $albumConfig->addComponent(new GridFieldSortableRows('SortOrder'));
            } else if (class_exists('GridFieldOrderableRows')) {
                $albumConfig->addComponent(new GridFieldOrderableRows('SortOrder'));
            }
            $albumField = new GridField('Albums', 'Albums', $this->Albums(),
                $albumConfig);
            $fields->addFieldToTab("Root.Albums", $albumField);
        } else {
            $fields->addFieldToTab(
                "Root.Albums",
                new HeaderField(
                _t("MagnificGalleryPage.ALBUMSNOTSAVED",
                    "You may add albums to your gallery once you have saved the page for the first time."),
                $headingLevel = "3"
                )
            );
        }

        // Build configuration fields
        $fields->addFieldToTab('Root', $configTab = new Tab('Configuration'));
        $configTab->setTitle(_t('MagnificGalleryPage.CONFIGURATION',
                'Configuration'));
        $fields->addFieldsToTab("Root.Configuration",
            array(
            $albumEffects = new DropdownField('AlbumEffect',
            _t('MagnificGalleryPage.ALBUMEFFECT', 'Album Effect'),
            array_combine(self::listEffects(), self::listEffects())),
            new NumericField('MediaPerPage',
                _t('MagnificGalleryPage.IMAGESPERPAGE',
                    'Number of images per page')),
            new LiteralField('FolderUsed',
                '<div class="message">'._t('MagnificGalleryPage.FOLDERUSED',
                    'Images will be saved in : %s',
                    array($this->RootFolder()->Filename)).'</div>')
        ));
        $albumEffects->setDescription(_t('MagnificGalleryPage.PREVIEWPAGE',
                'Preview effects <a target="_blank" href="http://tympanus.net/Development/HoverEffectIdeas/">here</a>'));

        return $fields;
    }
}

class MagnificGalleryPage_Controller extends Page_Controller
{
    private static $allowed_actions = array(
        'album',
        'rss'
    );

    /**
     * @var MagnificGalleryAlbum
     */
    protected $currentAlbum = null;

    public function init()
    {
        parent::init();
        Requirements::themedCSS('MagnificGallery', 'magnific-gallery');
        Requirements::javascript("magnific-gallery/javascript/imagegallery_init.js");
    }

    public function index()
    {
        if ($this->SingleAlbumView()) {
            return $this->album();
        } else {
            return $this->renderWith(array($this->data(), 'MagnificGalleryPage',
                    'Page'));
        }
    }

    protected function adjacentAlbum($dir)
    {
        $currentAlbum = $this->CurrentAlbum();
        if (empty($currentAlbum)) return null;

        $direction    = ($dir == "next") ? ">" : "<";
        $sort         = ($dir == "next") ? "ASC" : "DESC";
        $parentID     = Convert::raw2sql($this->ID);
        $adjacentID   = Convert::raw2sql($currentAlbum->ID);
        $adjacentSort = Convert::raw2sql($currentAlbum->SortOrder);
        // Get next/previous album by sort (or ID if sort values haven't been set)
        $filter       = "\"MagnificGalleryAlbum\".\"GalleryPageID\" = '$parentID' AND
			\"MagnificGalleryAlbum\".\"SortOrder\" {$direction} '$adjacentSort' OR (
				\"MagnificGalleryAlbum\".\"SortOrder\" = '$adjacentSort'
				AND \"MagnificGalleryAlbum\".\"ID\" {$direction} '$adjacentID'
			)";
        return MagnificGalleryAlbum::get()->where($filter)->sort("\"SortOrder\" $sort, \"ID\" $sort")->first();
    }

    public function NextAlbum()
    {
        return $this->adjacentAlbum("next");
    }

    public function PrevAlbum()
    {
        return $this->adjacentAlbum("prev");
    }

    public function album()
    {
        if (!MagnificGalleryItem::config()->image_crop) {
            Requirements::javascript("magnific-gallery/javascript/freewall.js");
        }
        Requirements::javascript("magnific-gallery/javascript/magnific-popup.js");
        Requirements::css("magnific-gallery/javascript/magnific-popup.css");
        if (!$this->CurrentAlbum()) {
            return $this->httpError(404);
        }
        return $this->renderWith(array($this->data().'_album', 'MagnificGalleryPage_album',
                'Page'));
    }

    public function PaginatedItems()
    {
        if (!$this->CurrentAlbum()) {
            return;
        }
        if (!$this->data()->MediaPerPage) {
            return $this->CurrentAlbum()->GalleryItems();
        }
        $list = new PaginatedList($this->CurrentAlbum()->GalleryItems(),
            $this->request);
        $list->setPageLength($this->data()->MediaPerPage);
        return $list;
    }

    /**
     * @return MagnificGalleryAlbum
     */
    public function CurrentAlbum()
    {
        if ($this->currentAlbum) return $this->currentAlbum;
        $params = Controller::curr()->getURLParams();
        if (!empty($params['ID'])) {
            return MagnificGalleryAlbum::get()->filter(array(
                    "URLSegment" => Convert::raw2sql($params['ID']),
                    "GalleryPageID" => $this->ID
                ))->first();
        }
        return false;
    }

    public function SingleAlbumView()
    {
        if ($this->Albums()->Count() == 1) {
            $this->currentAlbum = $this->Albums()->First();
            return true;
        }
        return false;
    }

    public function RssLink()
    {
        return $this->Link('rss');
    }

    public function rss()
    {
        $rss         = new RSSFeed(
            $list        = MagnificGalleryItem::get()->sort('Created DESC')->limit(10)->toArray(), // an SS_List containing your feed items
            $link        = Director::absoluteURL($this->RssLink()), // a HTTP link to this feed
            $title       = _t('MagnificGalleryPage.RSS_TITLE',
            'Recent images from %s',
            array(Siteconfig::current_site_config()->Title)), // title for this feed, displayed in RSS readers
            $description = _t('MagnificGalleryPage.RSS_DESCRIPTION',
            'Get recents images from our website') // description
        );
        // Outputs the RSS feed to the user.
        return $rss->outputToBrowser();
    }
}