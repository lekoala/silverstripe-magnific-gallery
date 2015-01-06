<?php

/**
 * @see Image
 */
class MagnificGalleryImage extends DataExtension
{

    public function generateRotateClockwise(GD $gd)
    {
        return $gd->rotate(90);
    }

    public function generateRotateCounterClockwise(GD $gd)
    {
        return $gd->rotate(270);
    }

    public function generateRotateAuto(GD $gd)
    {
        $exif = $this->Exif();

        if (!$exif) {
            return;
        }
        if (empty($exif['IFD0']['Orientation'])) {
            return;
        }

        switch ($exif['IFD0']['Orientation']) {
            case 3: // image upside down
                return $gd->rotate(180);
            case 6: // 90 rotate right & switch max sizes
                return $gd->rotate(-90);
            case 8: // 90 rotate left & switch max sizes
                return $gd->rotate(90);
        }

        return false;
    }

    public function Landscape()
    {
        return $this->owner->getWidth() > $this->owner->getHeight();
    }

    public function Portrait()
    {
        return $this->owner->getWidth() < $this->owner->getHeight();
    }

    function BackLinkTracking()
    {
        return false;
    }

    /**
     * @link http://www.v-nessa.net/2010/08/02/using-php-to-extract-image-exif-data
     * @return array
     */
    public function Exif()
    {
        //
        $image = $this->owner->AbsoluteURL;
        $d     = new ArrayList();
        $exif  = exif_read_data($image, 0, true);
        return $exif;
    }

    function updateCMSFields(\FieldList $fields)
    {
        // Rotate magic
        $f1 = new CheckboxField('RotateClockwise', 'Rotate Clockwise');
        $f2 = new CheckboxField('RotateCounterClockwise',
            'Rotate Counter Clockwise');
        $f3 = new CheckboxField('RotateAuto', 'Rotate Auto');

        $fields->addFieldToTab("Root.Main",
            $g = new FieldGroup(array($f1, $f2, $f3)));
        $g->setTitle('Rotate on save');
    }

    function replaceOriginal($filename)
    {
        $filename  = Director::baseFolder().'/'.$filename;
        $ownerFile = Director::baseFolder().'/'.$this->owner->Filename;
        if (!is_file($filename) || !is_readable($filename) || !is_writable($ownerFile)) {
            return;
        }
        unlink($ownerFile);
        rename($filename, $ownerFile);
        $this->owner->deleteFormattedImages();
    }

    function onAfterWrite()
    {
        parent::onAfterWrite();

        if (get_class(Controller::curr()) == 'CMSPageEditController') {
            if (!empty($_POST['RotateClockwise'])) {
                $image = $this->owner->getFormattedImage('RotateClockwise');
                $this->replaceOriginal($image->Filename);
            } elseif (!empty($_POST['RotateCounterClockwise'])) {
                $image = $this->owner->getFormattedImage('RotateCounterClockwise');
                $this->replaceOriginal($image->Filename);
            } else if (!empty($_POST['RotateAuto'])) {
                $image = $this->owner->getFormattedImage('RotateAuto');
                $this->replaceOriginal($image->Filename);
            }
        }
    }
}