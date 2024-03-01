# Carbon Fields Leaflet Map

A WordPress plugin that adds a [Leaflet](https://leafletjs.com/) Map field to [Carbon Fields](https://github.com/htmlburger/carbon-fields), as an alternative to the default Google Map. This plugin uses [Leaflet.GeoSearch](https://github.com/smeijer/leaflet-geosearch) under the hood.

## Installation for users

1. Make sure you’ve installed and activated the Carbon Fields plugin first.
2. Download the latest Carbon Fields Leaflet Map release and install it like you would any other WordPress plugin.

## Installation for developers

1. Use composer: `composer require MaxGruson/carbon-fields-leaflet-map --prefer-dist`
2. Run `npm install` and `npm run production` to have the assets compiled.

## Usage

### Registering the field

Use the name `leaflet_map` to register your field. E.g.:

```php
use Carbon_Fields\Container;
use Carbon_Fields\Field;

add_action( 'carbon_fields_register_fields', 'register_leaflet_map_field' );

function register_leaflet_map_field() {
   Container::make( 'post_meta', 'my_leaflet_map', __( 'My Leaflet Map', 'my-text-domain' ) )
    ->add_fields( [
      Field::make( 'leaflet_map', 'my_leaflet_map', __( 'My Leaflet map', 'my-text-domain' ) )
    ] );
}
```

### Customizing the field

This plugin offers a lot of customization options. See the list below, the [Leaflet docs](https://leafletjs.com/reference.html) and the [Leaflet.GeoSearch docs](https://smeijer.github.io/leaflet-geosearch/).

#### Search Providers

```php
Field::make(...)->set_search_provider( $searchProvider )
```

GeoSearch Provider. Needs a `(string)` that is one of following values:

* `Algolia`
* `Bing`
* `Esri`
* `Geocode`
* `Google`
* `LocationIQ`
* `OpenCage`
* `OpenStreetMap`
* `CivilDefenseMap`
* `Pelias`
* `MapBox`
* `GeoApi`
* `Geoapify`

See: [Leaflet.GeoSearch: Providers](https://github.com/smeijer/leaflet-geosearch/#providers).
Default: `OpenStreetMap`

#### Search Parameters

```php
Field::make(...)->set_search_params( $searchParams )
```

Options for the GeoSearch provider. This can include an API key. Needs an `(array)`.

See: [Leaflet.GeoSearch: Providers](https://smeijer.github.io/leaflet-geosearch/#providers).
Default:

```php
[
  'addressDetails' => 1,
  'viewbox'         => '3.032, 50.716, 7.273, 53.775', // The Netherlands
  'accept-language' => get_locale(),
];
```

#### Latitude and Longitude

```php
Field::make(...)->set_lat_lng( $lat, $lng )
Field::make(...)->set_lat( $lat )
Field::make(...)->set_lng( $lng )
```

Coordinates for the map to center on. Can be set together or individually. Needs a `(float)`. Default centers at Amsterdam, NL.

See: [Leaflet: Map Center](https://leafletjs.com/reference.html#map-center)
Default latitude: `52.3703`
Default longitude: `4.8937`

#### Address

```php
Field::make(...)->set_address( $address )
```

Text that describes the location the map is centered on. Will get displayed in a popup. Needs a `(string)`.

See: [Leaflet: Popup](https://leafletjs.com/reference.html#popup)
Default: `''`

#### Zoom

```php
Field::make(...)->set_zoom( $zoom )
```

The initial zoom level. Needs an `(integer)` between `1` and `14`.

See: [Leaflet: Map State Options](https://leafletjs.com/reference.html#map-zoom)
Default: `14`

#### Tile Layer URL

```php
Field::make(...)->set_url( $url )
```

Used to load and display tile layers on the map. Needs a `(string)`. Note that most tile servers require attribution, which you can set with:

```php
Field::make(...)->set_tile_layer_params( $tileLayerParams ) 
```

See: [Leaflet: TileLayer](https://leafletjs.com/reference.html#tilelayer)
Default: `'https://tile.openstreetmap.org/{z}/{x}/{y}.png'`;

#### Tile Layer Parameters

```php
Field::make(...)->set_tile_layer_params( $tileLayerParams ) 
```

Options for the map tile layer. This excludes the url, but includes the often required attribution. Needs an `(array)`.

See: [Leaflet: TileLayer](https://leafletjs.com/reference.html#tilelayer)
Default:

```php
[
  'attribution' => '&copy; <a href="http://www.openstreetmap.org/copyright">OpenStreetMap</a>',
];
```

#### Marker Icon

```php
Field::make(...)->set_marker_icon( $markerIcon )
```

Can be used to set a custom marker icon image and more. Needs an `(array)`.

See: [Leaflet: Icon](https://leafletjs.com/reference.html#icon)
Default: `null`

#### Marker Draggable

```php
Field::make(...)->set_marker_draggable( $markerDraggable )
```

Defines whether the marker is draggable with mouse/touch or not. When dragged and dropped the coordinates and address get updated, just like when using the search functionality. Needs a `(boolean)`.

See: [Leaflet: Marker](https://leafletjs.com/reference.html#marker-draggable)
Default: `true`

#### Search Field Style

```php
Field::make(...)->set_search_style( $searchStyle )
```

The display style for the GeoSearchControl input field. Needs a `(string)` that is either `'bar'`or `'button'`.

See: [Leaflet.GeoSearch: GeoSearchControl](https://github.com/smeijer/leaflet-geosearch#geosearchcontro)
Default: `'bar'`

#### Search Label Text

```php
Field::make(...)->set_search_label( $searchLabel )
```

The text within the search label. Needs a `(string)`.

See: [Leaflet.GeoSearch: GeoSearchControl](https://github.com/smeijer/leaflet-geosearch#geosearchcontro)
Default:

```php
__( 'Search...', 'carbon-fields-ui' )
```

#### Address Format


```php
Field::make(...)->set_address_format( $addressFormat )
```

Reformats the returned address when searching. Can be used to match the common syntax of addresses in a language. NOTE: This function is a bit hacky. It needs a `(string)` that contains a JavaScript callback. Default is only tested with [OSM Nominatim](https://nominatim.org/release-docs/develop/api/Output/#addressdetails) and adjusts the address string to a format common in The Netherlands, Belgium and Germany.

Badly documented, see: [Leaflet.GeoSearch: feat: add option to format results](https://github.com/smeijer/leaflet-geosearch/pull/256)
Default:

```php
'({result})=>{
  let addressString = "";
  if( ["nl", "de", "be" ].includes( "' . substr( get_locale(), 0, 2 ) . '" ) ) { 
    const address = result.raw.address;
    if( typeof address.road !== "undefined" ) {
      addressString += `${address.road}`;
      if( typeof address.house_number !== "undefined" ) {
        addressString += ` ${address.house_number}`;
      }
      addressString += "<br>";
    }
    if( typeof address.postcode !== "undefined" ) {
      addressString += `${address.postcode}  `;
    }
    if( typeof address.city !== "undefined" ) {
      addressString += `${address.city}`;
    } else if( typeof address.town !== "undefined" ) {
      addressString += `${address.town}`;
    } else if( typeof address.village !== "undefined" ) {
      addressString += `${address.village}`;
    }
  } else {
    addressString = result.label;
  }
  return addressString;
}'
```


### Using the field values

The _latitude_ `(float)`, _longitude_ `(float)`, and _address_`(string)` get saved to the database. You can use them on the frontend of your website as you wish. For example, if you wanted to show a Leaflet Map you might use the following code.

In your WordPress template file:

```php
<?php
$map = carbon_get_the_post_meta( 'my-leaflet-map' );
$lat     = $map['lat'];
$lng     = $map['lng'];
$address = $map['address'];
?>
<div 
  id="my-map"
  data-lat="<?php echo esc_attr( $lat ); ?>"
  data-lng="<?php echo esc_attr( $lng ); ?>"
  data-address="<?php echo esc_attr( $address ); ?>"
></div>
```

In `functions.php`:

```php
<?php
wp_enqueue_style( 
  'leaflet-style',
  'https://unpkg.com/leaflet@1.9.4/dist/leaflet.css',
  [],
  '1.9.4'
);
wp_enqueue_script( 
  'leaflet-script',
  'https://unpkg.com/leaflet@1.9.4/dist/leaflet.js',
  [],
  '1.9.4'
);
wp_enqueue_script(
  'init-leaflet',
  get_theme_file_uri( get_asset_file( 'init-leaflet.js' ) ),
  [ 'leaflet' ],
  filemtime( get_theme_file_path( get_asset_file( 'init-leaflet.js' ) ) ),
  true
);
```

In `init-leaflet.js`:

```js
const mapEL = document.querySelector( '#my-map' );
const lat = mapEL.getAttribute( 'data-lat' );
const lng = mapEL.getAttribute( 'data-lng' );
const address = mapEL.getAttribute( 'data-address' );

const map = L.map( mapEL, {
  center: [lat, lng]
} );

L.tileLayer( 'https://tile.openstreetmap.org/{z}/{x}/{y}.png', {
  attribution: '&copy; <a href="http://www.openstreetmap.org/copyright">OpenStreetMap</a>'
} ).addTo( map );

const marker = L.marker( [lat, lng] );
marker.addTo( map );
marker.bindPopup(address).openPopup();
```
