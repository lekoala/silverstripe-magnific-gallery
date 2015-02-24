<?php

class MagnificGalleryItem extends DataObject
{
    private static $db             = array(
        'Caption' => 'Text',
        'VideoLink' => 'Varchar(255)',
        'SortOrder' => 'Int'
    );
    private static $has_one        = array(
        'GalleryPage' => 'MagnificGalleryPage',
        'Album' => 'MagnificGalleryAlbum',
        'Image' => 'Image'
    );
    private static $default_sort   = 'SortOrder ASC';
    private static $summary_fields = array(
        'Image.CMSThumbnail' => 'Image',
        'Caption' => 'Image Caption',
        'IsVideo' => 'Video?'
    );

    public function getTitle()
    {
        if ($this->Caption) {
            return $this->dbObject('Caption')->FirstSentence();
        }
        if ($image = $this->Image()) {
            return $image->Title;
        }
        return parent::getTitle();
    }

    public function validate()
    {
        $result = parent::validate();

        if ($this->VideoLink) {
            // Rewrite common patterns
            $this->VideoLink = str_replace('https://vimeo', 'http://vimeo',
                $this->VideoLink);
            $this->VideoLink = str_replace('http://youtu.be/',
                'http://www.youtube.com/watch?v=', $this->VideoLink);

            // Validate
            $patterns = array(
                'http://www.youtube.com/watch?v=',
                'http://vimeo.com/',
                'http://www.dailymotion.com/embed/video/',
            );
            $found    = false;
            foreach ($patterns as $pattern) {
                if (strpos($this->VideoLink, $pattern) === 0) {
                    $found = true;
                }
            }
            if (!$found) {
                $result->error(_t('MagnificGalleryItem.VIDEOPROVIDERERR',
                        'Your video link format is not supported'));
            }
        }

        return $result;
    }

    public function getCMSFields()
    {
        $fields = new FieldList(new TabSet('Root'));

        // Details
        $fields->addFieldToTab('Root.Main',
            new TextareaField('Caption',
            _t('MagnificGalleryItem.CAPTION', 'Caption')));

        // Create image
        $imageField = new UploadField('Image');
        $imageField->getValidator()->setAllowedExtensions(File::config()->app_categories['image']);
        $imageField->setFolderName($this->Album()->getUploadFolder());
        $fields->addFieldToTab('Root.Main', $imageField);

        // Details
        $fields->addFieldToTab('Root.Main',
            new TextField('VideoLink',
            _t('MagnificGalleryItem.VIDEOLINK', 'Video link')));

        return $fields;
    }

    public function MagnificClass()
    {
        $type = $this->IsVideo() ? 'iframe' : 'image';
        return 'mfp-'.$type;
    }

    public function IsVideo()
    {
        if ($this->VideoLink) {
            return true;
        }
        return false;
    }

    public function FormattedImage()
    {
        /* @var $image Image */
        $image = $this->Image();
        if (!$image) {
            return null;
        }

        if (self::config()->image_crop) {
            return $image->CroppedImage(self::config()->image_width,
                    self::config()->image_height);
        }

        if ($image->Landscape()) {
            return $image->SetWidth(self::config()->image_width);
        } else {
            return $image->SetHeight(self::config()->image_height);
        }
    }

    public function Link()
    {
        return $this->Album()->Link();
    }

    public function VideoLinkAutoplay() {
        if(strpos($this->VideoLink, 'vimeo')) {
            return $this->VideoLink . '?autoplay=1';
        }
        return $this->VideoLink;
    }

    public function forTemplate()
    {
        return $this->renderWith('AlbumItem');
    }

    public function canDelete($member = null)
    {
        return Permission::check(self::config()->delete_permission, 'any',
                $member);
    }

    function onBeforeWrite()
    {
        parent::onBeforeWrite();
        if (!$this->SortOrder) {
            $this->SortOrder = self::get()->max('SortOrder') + 1;
        }
    }
}