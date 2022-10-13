<?php

namespace Tobycroft\AossSdk\ImageRequestbuilder;

class ImageCreateText
{
    public $type = "text";

    public string $position = "lt";
    public string $text = "";
    public int $size = 13;
    public mixed $x = 0;
    public mixed $y = 0;
    public string $url = "";
    public string $font_color = "000000";
}