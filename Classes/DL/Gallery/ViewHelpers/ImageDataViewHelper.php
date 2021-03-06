<?php
namespace DL\Gallery\ViewHelpers;

/***************************************************************
 *  Copyright (C) 2015 Daniel Lienert
 *
 *  This script is free software: you can redistribute it and/or modify
 *  it under the terms of the GNU Lesser General Public License as published
 *  by the Free Software Foundation, either version 3 of the License, or
 *  (at your option) any later version.
 *
 *  This script is distributed in the hope that it will be useful,
 *  but WITHOUT ANY WARRANTY; without even the implied warranty of
 *  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *  GNU Lesser General Public License for more details.
 *
 *  You should have received a copy of the GNU General Public License
 *  along with this program.  If not, see <http://www.gnu.org/licenses/>.
 *
 *  This copyright notice MUST APPEAR in all copies of the script!
 ***************************************************************/

use TYPO3\Flow\Annotations as Flow;
use TYPO3\Fluid\Core\ViewHelper\AbstractViewHelper;
use TYPO3\Media\Domain\Model\ThumbnailConfiguration;

class ImageDataViewHelper extends AbstractViewHelper
{

    /**
     * @var array
     */
    protected $settings;

    /**
     * @Flow\Inject
     * @var \TYPO3\Media\Domain\Service\ThumbnailService
     */
    protected $thumbnailService;

    /**
     * @Flow\Inject
     * @var \TYPO3\Media\Domain\Service\AssetService
     */
    protected $assetService;

    /**
     * name of the tag to be created by this view helper
     *
     * @var string
     */
    protected $tagName = 'img';

    /**
     * @param array $settings
     * @return void
     */
    public function injectSettings(array $settings)
    {
        $this->settings = $settings;
    }


    /**
     * @return void
     */
    public function initializeArguments()
    {
        parent::initializeArguments();
        $this->registerArgument('theme', 'string', 'The name of a gallery theme', false);
        $this->registerArgument('imageVariant', 'string', 'The name of a defined resolution', false);
        $this->registerArgument('key', 'string', 'The key of meta data array', false);
    }


    public function render(\TYPO3\Media\Domain\Model\ImageInterface $image = null, $width = null, $maximumWidth = null, $height = null, $maximumHeight = null, $allowCropping = false, $allowUpScaling = false)
    {

        if ($this->hasArgument('theme') && $this->hasArgument('imageVariant')) {
            $themeSettings = $this->getSettingsForCurrentTheme($this->arguments['theme']);

            $imageVariantSettings = $themeSettings['imageVariants'][$this->arguments['imageVariant']];

            $width = $imageVariantSettings['width'];
            $maximumWidth = $imageVariantSettings['maximumWidth'];
            $height = $imageVariantSettings['height'];
            $maximumHeight = $imageVariantSettings['maximumHeight'];

            $allowCropping = $imageVariantSettings['allowCropping'];
            $allowUpScaling = $imageVariantSettings['allowUpScaling'];
        }


        $thumbnailConfiguration = new ThumbnailConfiguration($width, $maximumWidth, $height, $maximumHeight, $allowCropping, $allowUpScaling);
        $imageData = $this->assetService->getThumbnailUriAndSizeForAsset($image, $thumbnailConfiguration);

        $imageData['title'] = $image->getTitle();
        $imageData['caption'] = $image->getCaption();

        if($this->hasArgument('key')) {
            return $imageData[$this->arguments['key']];
        } else {
            return $imageData;
        }

    }


    /**
     * @param $theme
     * @return mixed
     */
    protected function getSettingsForCurrentTheme($theme)
    {
        return $this->settings['themes'][$theme]['themeSettings'];
    }
}