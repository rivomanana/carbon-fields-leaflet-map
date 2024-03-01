<?php
use Carbon_Fields\Carbon_Fields;
use Carbon_Field_Leaflet_Map\Leaflet_Map_Field;

if (!function_exists('add_action')) {
  return;
}

define( 'Carbon_Field_Leaflet_Map\\DIR', __DIR__ );

add_action( 'after_setup_theme', function () {
  Carbon_Fields::extend( Leaflet_Map_Field::class, function ( $container ) {
      return new Leaflet_Map_Field(
        $container['arguments']['type'],
        $container['arguments']['name'],
        $container['arguments']['label']
      );
  } );
}, 99 );
