<?php

class MagnificGalleryAlbum extends DataObject
{
    private static $db             = array(
        'AlbumName' => 'Varchar(255)',
        'Description' => 'Text',
        'SortOrder' => 'Int',
        'URLSegment' => 'Varchar(255)'
    );
    private static $has_one        = array(
        'CoverImage' => 'Image',
        'GalleryPage' => 'MagnificGalleryPage',
        'Folder' => 'Folder'
    );
    private static $has_many       = array(
        'GalleryItems' => 'MagnificGalleryItem'
    );
    private static $summary_fields = array(
        'CoverImage.CMSThumbnail' => 'Cover Image',
        'AlbumName' => 'Album Name',
        'Description' => 'Description'
    );
    private static $default_sort   = '"SortOrder" ASC';

    public function getTitle()
    {
        if ($this->AlbumName) return $this->AlbumName;
        return parent::getTitle();
    }

    public function getUploadFolder()
    {
        if (!$this->ID) {
            return;
        }
        if (!$this->GalleryPageID) {
            return;
        }
        $folder = trim($this->GalleryPage()->RootFolder()->Filename, '/').'/'.$this->URLSegment;
        return ltrim($folder, 'assets/');
    }

    public function Effect()
    {
        $effect = self::config()->album_effect;
        if (!$effect) {
            $effect                      = self::config()->album_effect = $this->GalleryPage()->AlbumEffect;
        }
        return $effect;
    }

    public function getCMSFields()
    {
        // If the album is not created yet, ask first for an album title to create the folder
        if (!$this->ID) {
            $fields = new FieldList();

            $fields->push(new TextField('AlbumName',
                _t('MagnificGalleryAlbum.ALBUMTITLE', 'Album Title'), null, 255));
            $fields->push(new LiteralField('AlbumSaveInfos',
                _t('MagnificGalleryAlbum.ALBUMSAVEINFOS',
                    'You can add images and a description once the album is saved'),
                null, 255));

            return $fields;
        }


        $fields = new FieldList(new TabSet('Root'));

        // Image listing
        $galleryConfig = GridFieldConfig_RecordEditor::create();

        // Enable bulk image loading if necessary module is installed
        // @see composer.json/suggests
        if (class_exists('GridFieldBulkManager')) {
            $galleryConfig->addComponent(new GridFieldBulkManager());
        }
        if (class_exists('GridFieldBulkUpload')) {
            $galleryConfig->addComponents($imageConfig = new GridFieldBulkUpload('Image'));
            $imageConfig->setUfSetup('setFolderName', $this->getUploadFolder());
        }

        // Enable image sorting if necessary module is installed
        // @see composer.json/suggests
        if (class_exists('GridFieldSortableRows')) {
            $galleryConfig->addComponent(new GridFieldSortableRows('SortOrder'));
        } else if (class_exists('GridFieldOrderableRows')) {
            $galleryConfig->addComponent(new GridFieldOrderableRows('SortOrder'));
        }

        $galleryField = new GridField('GalleryItems', 'Gallery Items',
            $this->GalleryItems(), $galleryConfig);
        $fields->addFieldToTab('Root.Main', $galleryField);

        // Details
        $thumbnailField = new UploadField('CoverImage',
            _t('MagnificGalleryAlbum.COVERIMAGE', 'Cover Image'));
        $thumbnailField->getValidator()->setAllowedExtensions(File::config()->app_categories['image']);
        $thumbnailField->setFolderName($this->getUploadFolder());

        $fields->addFieldsToTab('Root.Album',
            array(
            new TextField('AlbumName',
                _t('MagnificGalleryAlbum.ALBUMTITLE', 'Album Title'), null, 255),
            new TextareaField('Description',
                _t('MagnificGalleryAlbum.DESCRIPTION', 'Description')),
            $thumbnailField
        ));

        return $fields;
    }

    public function Link()
    {
        return Controller::join_links(
                $this->GalleryPage()->Link('album'), $this->URLSegment
        );
    }

    public function LinkingMode()
    {
        $params = Controller::curr()->getURLParams();
        return (!empty($params['ID']) && $params['ID'] == $this->URLSegment) ? "current"
                : "link";
    }

    /**
     * Count the number of images in the gallery
     * @return int
     */
    public function ImageCount()
    {
        return $this->GalleryItems()->Count();
    }

    public function FormattedCoverImage()
    {
        $page = $this->GalleryPage();
        $w    = $this->CoverWidth();
        $h    = $this->CoverHeight();

        return $this->CoverImage()->CroppedImage($w, $h);
    }

    public function CoverWidth()
    {
        return self::config()->cover_width;
    }

    public function CoverHeight()
    {
        return self::config()->cover_height;
    }

    function onBeforeWrite()
    {
        parent::onBeforeWrite();
        $this->checkURLSegment();
        $this->checkFolder();
        if (!$this->SortOrder) {
            $this->SortOrder = self::get()->max('SortOrder') + 1;
        }
        // Autoset cover image based on gallery contents
        if (!$this->CoverImageID && $this->ImageCount()) {
            $this->CoverImageID = $this->GalleryItems()->First()->ImageID;
        }
    }

    public function validate()
    {
        $result = parent::validate();

        if (!trim($this->AlbumName)) {
            $result->error(_t('MagnificGalleryAlbum.MUSTHAVETITLE',
                    'You must set a title'));
        }

        return $result;
    }

    function checkFolder()
    {
        $folderName = $this->getUploadFolder();

        if (!$folderName) {
            return;
        }
        $folder = Folder::find_or_make($folderName);
        if ($this->FolderID && $folder->ID != $this->FolderID) {
            // We need to rename current folder
            $this->Folder()->setFilename($folder->Filename);
            $this->Folder()->write();
            $folder->deleteDatabaseOnly(); //Otherwise we keep a stupid clone that will be used as the parent
        } else {
            $this->FolderID = $folder->ID;
        }
    }

    public function checkURLSegment()
    {
        $filter           = URLSegmentFilter::create();
        $this->URLSegment = $filter->filter($this->AlbumName);
    }

    function onBeforeDelete()
    {
        parent::onBeforeDelete();
        $this->GalleryItems()->removeAll();
        if ($this->FolderID && $this->Folder()->ID) {
            $this->Folder()->delete();
        }
        if ($this->CoverImageID && $this->CoverImage()->ID) {
            $this->CoverImage()->delete();
        }
    }
}