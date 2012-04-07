<?php

/*
 * Heroku Memcache Addon
 * heroku addons:add memcache
 */
if (isset($_SERVER['MEMCACHE_SERVERS']))
{
   $config['memcached'] = array(
      'hostname' => $_SERVER['MEMCACHE_SERVERS'],
      'port' => 11211,
      'weight' => 1
   );
}

?>
