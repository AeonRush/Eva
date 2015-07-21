<?php
    
return 
array
(
	# '^app/(.*)/(.+)$:get'               => 'files/app?path=$1&filename=$2',
	'^app/(.*)$:get'                    => 'files/app?path=$1',
	'^fragment/(.*)$:get'               => 'files/fragment?path=$1',
    '^short/(.+)$:get'                  => 'files/short?path=$1',
	'^(.+)$:get'                        => 'files/all?path=$1',
);
