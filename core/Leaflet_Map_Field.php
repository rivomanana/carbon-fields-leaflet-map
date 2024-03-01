<?php
// phpcs:disable WordPress.NamingConventions.ValidVariableName.PropertyNotSnakeCase, WordPress.NamingConventions.ValidVariableName.VariableNotSnakeCase, WordPress.NamingConventions.ValidVariableName.UsedPropertyNotSnakeCase

namespace Carbon_Field_Leaflet_Map;

use Carbon_Fields\Field\Field;
use Carbon_Fields\Value_Set\Value_Set;

/**
 * Class for a Leaflet map combined with Leaflet Geosearch field.
 * Allows to manually select a pin, or to position a pin based on a specified address.
 * Lat, lng, and address are saved to database.
 */
class Leaflet_Map_Field extends Field {
  /**
   * GeoSearch provider
   * @var string Algolia|Bing|Esri|Geocode|Google|LocationIQ|OpenCage|OpenStreetMap|CivilDefenseMap|Pelias|MapBox|GeoApi|Geoapify
   * @link https://github.com/smeijer/leaflet-geosearch/#providers
   */
  protected $searchProvider = 'OpenStreetMap';

  /**
   * Options for the GeoSearch provider. This can include an API key. Empty array gets overwritten.
   * @var array
   * @link https://smeijer.github.io/leaflet-geosearch/#providers
   */
  protected $searchParams = [];

  /**
   * Location latitude.
   * @var float
   * @link https://leafletjs.com/reference.html#map-center
   */
  protected $lat = 52.3703;

  /**
   * Location longitude.
   * @var float
   * @link https://leafletjs.com/reference.html#map-center
   */
  protected $lng = 4.8937;

  /**
   * Location address. Should always be combined with setting the Lat en Lng.
   * @var string
   */
  protected $address = '';

  /**
   * Initial map zoom level.
   * @var int <1,14>
   * @link https://leafletjs.com/reference.html#map-zoom
   */
  protected $zoom = 14;

  /**
   * Map tile layer url.
   * @var string
   * @link https://leafletjs.com/reference.html#tilelayer
   */
  protected $url = 'https://tile.openstreetmap.org/{z}/{x}/{y}.png';

  /**
   * Options for the map tile layer. This excludes the url, but includes the often required attribution. Empty array gets overwritten.
   * @var array
   * @link https://leafletjs.com/reference.html#tilelayer
   */
  protected $tileLayerParams = [];

  /**
   * Custom icon options.
   * @var array
   * @link https://leafletjs.com/reference.html#icon
   */
  protected $markerIcon = [];

  /**
   * Whether the marker is draggable with mouse/touch or not.
   * @var bool
   * @link https://leafletjs.com/reference.html#marker-draggable
   */
  protected $markerDraggable = true;

  /**
   * The display style for the GeoSearchControl input field.
   * @var string bar|button
   * @link https://github.com/smeijer/leaflet-geosearch#geosearchcontrol
   */
  protected $searchStyle = 'bar';

  /**
   * The text within the search label. Empty string gets overwritten.
   * @var string
   */
  protected $searchLabel = '';

  /**
   * A JavaScript callback function that reformats the returned address string. Empty string gets overwritten. Default is only tested with OSM Nominatim.
   * @var string
   */
  protected $addressFormat = '';

  /**
   * Functions accessible to customize experience.
   */
  public function set_search_provider( $searchProvider ) {
    $this->searchProvider = $searchProvider;
    return $this;
  }
  public function set_search_params( $searchParams ) {
    $this->searchParams = $searchParams;
    return $this;
  }
  public function set_lat_lng( $lat, $lng ) {
    $this->lat = $lat;
    $this->lng = $lng;
    return $this;
  }
  public function set_lat( $lat ) {
    $this->lat = $lat;
    return $this;
  }
  public function set_lng( $lng ) {
    $this->lng = $lng;
    return $this;
  }
  public function set_address( $address ) {
    $this->address = $address;
    return $this;
  }
  public function set_zoom( $zoom ) {
    $this->zoom = $zoom;
    return $this;
  }
  public function set_url( $url ) {
    $this->url = $url;
    return $this;
  }
  public function set_tile_layer_params( $tileLayerParams ) {
    $this->tileLayerParams = $tileLayerParams;
    return $this;
  }
  public function set_marker_icon( $markerIcon ) {
    $this->markerIcon = $markerIcon;
    return $this;
  }
  public function set_marker_draggable( $markerDraggable ) {
    $this->markerDraggable = $markerDraggable;
    return $this;
  }
  public function set_search_style( $searchStyle ) {
    $this->searchStyle = $searchStyle;
    return $this;
  }
  public function set_search_label( $searchLabel ) {
    $this->searchLabel = $searchLabel;
    return $this;
  }
  public function set_address_format( $addressFormat ) {
    $this->addressFormat = $addressFormat;
    return $this;
  }

  /**
   * Create a field from a certain type with the specified label.
   *
   * @param string $type  Field type
   * @param string $name  Field name
   * @param string $label Field label
   */
  public function __construct( $type, $name, $label ) {
    $this->set_value_set(new Value_Set(Value_Set::TYPE_MULTIPLE_PROPERTIES, [
      'lat' => '',
      'lng' => '',
      'address' => '',
    ]));

    parent::__construct( $type, $name, $label );
  }

  /**
   * Prepare the field type for use.
   * Called once per field type when activated.
   */
  public static function field_type_activated() {
    $dir    = \Carbon_Field_Leaflet_Map\DIR . '/languages/';
    $locale = get_locale();
    $path   = $dir . $locale . '.mo';
    load_textdomain( 'carbon-fields-leaflet-map', $path );
  }

  /**
   * Enqueue scripts and styles in admin.
   * Called once per field type.
   */
  public static function admin_enqueue_scripts() {
    $root_uri = \Carbon_Fields\Carbon_Fields::directory_to_url( \Carbon_Field_Leaflet_Map\DIR );

    // Enqueue JS
    wp_enqueue_script( 'carbon-fields-leaflet-map', $root_uri . '/assets/build/bundle.js', filemtime( WP_PLUGIN_DIR . '/carbon-fields-leaflet-map/assets/build/bundle.js' ), true );

    // Enqueue CSS
    wp_enqueue_style( 'leaflet', 'https://unpkg.com/leaflet@1.9.4/dist/leaflet.css' );
    wp_enqueue_style( 'leaflet-geosearch', 'https://unpkg.com/leaflet-geosearch@3.11.0/dist/geosearch.css', [ 'leaflet' ] );
    wp_enqueue_style( 'carbon-fields-leaflet-map', $root_uri . '/assets/build/bundle.css', [ 'editor-buttons' ] );
  }

  /**
   * Load the field value from an array of input element values based on it's name.
   * This is what stores the field in the database.
   *
   * @param  array $input Array of field names and values.
   * @return self  $this
   */
  public function set_value_from_input( $input ) {
    if ( ! isset( $input[ $this->get_name() ] ) ) {
      $this->set_value( null );
      return $this;
    }

    $value_set = [
      'lat' => (float) $this->lat,
      'lng' => (float) $this->lng,
      'address' => $this->address,
    ];

    foreach ( $value_set as $key => $v ) {
      if ( isset( $input[ $this->get_name() ][ $key ] ) ) {
        $value_set[ $key ] = $input[ $this->get_name() ][ $key ];
      }
    }

    $this->set_value( $value_set );

    return $this;
  }

  /**
   * Returns an array that holds the field data, suitable for JSON representation.
   * These return the values to the frontend.
   *
   * @param bool $load  Should the value be loaded from the database or use the value from the current instance.
   * @return array
   */
  public function to_json( $load ) {
    $field_data = parent::to_json( $load );

    $field_data['value']['lat'] = (float) $field_data['value']['lat'];
    $field_data['value']['lng'] = (float) $field_data['value']['lng'];

    if ( [] === $this->searchParams ) {
      $this->searchParams = [
        'addressdetails'  => 1,
        'viewbox'         => '3.032, 50.716, 7.273, 53.775', // The Netherlands
        'accept-language' => get_locale(),
      ];
    }

    if ( [] === $this->markerIcon ) {
      $this->markerIcon = null;
    }

    if ( '' === $this->addressFormat ) {
      $this->addressFormat = '({result})=>{
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
      }';
    }

    if ( '' == $this->searchLabel ) {
      $this->searchLabel = __( 'Search...', 'carbon-fields-ui' );
    }

    if ( [] == $this->tileLayerParams ) {
      $this->tileLayerParams = [
        'attribution' => '&copy; <a href="http://www.openstreetmap.org/copyright">OpenStreetMap</a>',
      ];
    }

    $field_data = array_merge( $field_data, [
      'searchProvider'   => $this->searchProvider,
      'searchParams'     => $this->searchParams,
      'lat'              => (float) $this->lat,
      'lng'              => (float) $this->lng,
      'address'          => $this->address,
      'zoom'             => (int) $this->zoom,
      'url'              => $this->url,
      'tileLayerParams'  => $this->tileLayerParams,
      'markerIcon'       => $this->markerIcon,
      'markerDraggable'  => (bool) $this->markerDraggable,
      'searchStyle'      => $this->searchStyle,
      'searchLabel'      => $this->searchLabel,
      'addressFormat'    => $this->addressFormat,
    ] );

    return $field_data;
  }
}
