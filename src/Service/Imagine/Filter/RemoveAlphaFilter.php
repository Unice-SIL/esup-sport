<?php


namespace App\Service\Imagine\Filter;

use Imagine\Image\ImageInterface;
use Imagine\Image\ImagineInterface;
use Imagine\Image\Point;
use Liip\ImagineBundle\Imagine\Filter\Loader\LoaderInterface;

class RemoveAlphaFilter implements LoaderInterface
{
    private $imagine;

    public function __construct(
        ImagineInterface $imagine
    ) 
    {
        $this->imagine = $imagine;
    }

    public function load(ImageInterface $image, array $options = [])
    {
        $topLeft = new Point(0, 0);
        $color = isset($options['color']) ? $options['color'] : '#ffffff';
        $size = $image->getSize();
        $background = $image->palette()->color($color, null);

        $canvas = $this->imagine->create($size, $background);

        return $canvas->paste($image, $topLeft);
    }
}
