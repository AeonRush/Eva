<?php

return array(
    'layout' => 'default',
    'ngn:debug' => false,
    /// db connection options
    'db:enabled' => true,
    'db:server' => '127.0.0.1',
    'db:name' => 'dandelion',
    'db:user' => 'root',
    'db:password' => '123321',
    ///
    'auth:session-key'  => 'auth:'.md5('cat-on-keyboard-^_^-'.__FILE__),
    /// Engine options
    /// Static cache
    /// -1 - no-cache, must-revalidate
    /// 0 - nothing
    /// 1 - enable caching
    'eva:staticCache' => 1,
    'eva:urlSection' => '/',
    'eva:languages' => array() /// Simple
);

/// 2015 : AeonRush
