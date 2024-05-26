<?php

// autoload_static.php @generated by Composer

namespace Composer\Autoload;

class ComposerStaticInit045436ed3d4685d5443fa27e7dfd0a5c
{
    public static $prefixLengthsPsr4 = array (
        'S' => 
        array (
            'Stripe\\' => 7,
        ),
    );

    public static $prefixDirsPsr4 = array (
        'Stripe\\' => 
        array (
            0 => __DIR__ . '/..' . '/stripe/stripe-php/lib',
        ),
    );

    public static $classMap = array (
        'Composer\\InstalledVersions' => __DIR__ . '/..' . '/composer/InstalledVersions.php',
    );

    public static function getInitializer(ClassLoader $loader)
    {
        return \Closure::bind(function () use ($loader) {
            $loader->prefixLengthsPsr4 = ComposerStaticInit045436ed3d4685d5443fa27e7dfd0a5c::$prefixLengthsPsr4;
            $loader->prefixDirsPsr4 = ComposerStaticInit045436ed3d4685d5443fa27e7dfd0a5c::$prefixDirsPsr4;
            $loader->classMap = ComposerStaticInit045436ed3d4685d5443fa27e7dfd0a5c::$classMap;

        }, null, ClassLoader::class);
    }
}
