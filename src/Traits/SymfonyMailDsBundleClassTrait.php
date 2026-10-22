<?php

namespace Wexample\SymfonyMailDs\Traits;

use Wexample\SymfonyHelpers\Traits\BundleClassTrait;
use Wexample\SymfonyMailDs\WexampleSymfonyMailDsBundle;

trait SymfonyMailDsBundleClassTrait
{
    use BundleClassTrait;

    public static function getBundleClassName(): string
    {
        return WexampleSymfonyMailDsBundle::class;
    }
}
