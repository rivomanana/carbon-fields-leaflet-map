/**
 * External dependencies.
 */
import { Component, createRef } from '@wordpress/element';
import { __ } from '@wordpress/i18n';
import L from 'leaflet';
import { AlgoliaProvider, BingProvider, CivilDefenseMapProvider, EsriProvider, GeoApiFrProvider, GeoSearchControl, GeoapifyProvider, GeocodeEarthProvider, GoogleProvider, LocationIQProvider, MapBoxProvider, OpenCageProvider, OpenStreetMapProvider, PeliasProvider } from 'leaflet-geosearch';

class MapField extends Component {
  /**
   * Keeps references to the DOM node.
   *
   * @type {Object}
   */
  node = createRef();

  /**
   * Lifecycle hook.
   *
   * @return {void}
   */
  componentDidMount() {
    this.setupMap();
    this.setupMapEvents();
  }

  /**
   * Lifecycle hook.
   *
   * @return {void}
   */
  componentDidUpdate() {
    this.map.invalidateSize();
  }

  /**
   * Lifecycle hook.
   *
   * @return {void}
   */
  componentWillUnmount() {
    // TODO: remove event listeners to prevent memory leak in older browsers
  }

  /**
   * Initializes the map into placeholder element.
   *
   * @return {void}
   */
  setupMap() {
    let searchProvider = this.props.value.searchProvider || this.props.field.searchProvider;

    const lat = this.props.value.lat || this.props.field.lat;
    const lng = this.props.value.lng || this.props.field.lng;

    const zoom = this.props.value.zoom || this.props.field.zoom;

    const url = this.props.value.url || this.props.field.url;

    const searchStyle = this.props.value.searchStyle || this.props.field.searchStyle;
    const searchLabel = this.props.value.searchLabel || this.props.field.searchLabel;
    const addressFormat = this.props.value.addressFormat || this.props.field.addressFormat;
    const address = this.props.value.address || this.props.field.address;

    const markerDraggable = this.props.value.markerDraggable || this.props.field.markerDraggable;
    const markerIcon = this.props.value.markerIcon || this.props.field.markerIcon;

    const searchParams = this.props.value.searchParams || this.props.field.searchParams;
    const tileLayerParams =  this.props.value.tileLayerParams || this.props.field.tileLayerParams;

    switch ( searchProvider ) {
      case "Algolia":
        searchProvider = new AlgoliaProvider( { params: searchParams } );
        break;
      case "Bing":
        searchProvider = new BingProvider( { params: searchParams } );
        break;
      case "Esri":
        searchProvider = new EsriProvider( { params: searchParams } );
        break;
      case "GeocodeEarth":
        searchProvider = new GeocodeEarthProvider( { params: searchParams } );
        break;
      case "Google":
        searchProvider = new GoogleProvider( { params: searchParams } );
        break;
      case "LocationIQ":
        searchProvider = new LocationIQProvider( { params: searchParams } );
        break;
      case "OpenCage":
        searchProvider = new OpenCageProvider( { params: searchParams } );
        break;
      case "OpenStreetMap":
        searchProvider = new OpenStreetMapProvider( { params: searchParams } );
        break;
      case "CivilDefenseMap":
        searchProvider = new CivilDefenseMapProvider( { params: searchParams } );
        break;
      case "Pelias":
        searchProvider = new PeliasProvider( { params: searchParams } );
        break;
      case "MapBox":
        searchProvider = new MapBoxProvider( { params: searchParams } );
        break;
      case "GeoApiFr":
        searchProvider = new GeoApiFrProvider( { params: searchParams } );
        break;
      case "Geoapify":
        searchProvider = new GeoapifyProvider( { params: searchParams } );
        break;
      default:
        console.error( "An invalid provider was supplied to Leaflet GeoSearch!" );
        break;
    }

    const addressFormatFunction = eval?.( `"use strict";(${addressFormat})` );

    this.searchControl = new GeoSearchControl( {
      provider: searchProvider,
      style: searchStyle,
      showMarker: true,
      showPopup: true,
      popupFormat: addressFormatFunction,
      resultFormat: addressFormatFunction,
      searchLabel: searchLabel,
      animateZoom: true,
    } );

    this.map = L.map( this.node.current, {
      center: [ lat, lng ],
      zoom,
      scrollWheelZoom: false,
    } );
    L.tileLayer( url, tileLayerParams ).addTo( this.map );

    this.map.addControl( this.searchControl );

    // Add marker
    if( address ) {
      if ( markerIcon ) {
        this.marker = L.marker( [ lat, lng ],
          {
            draggable: markerDraggable,
            icon: L.icon( markerIcon )
          }
        );
      } else {
        this.marker = L.marker( [ lat, lng ],
          {
            draggable: markerDraggable
          }
        );
      }
      this.marker.addTo( this.map );
      // Add marker popup

      this.marker.bindPopup( address );
      this.map.whenReady( () => { setTimeout( () => {
        this.marker.openPopup();
        this.map.setView( this.marker.getLatLng() ).panBy([0,-50] );
      }, 500 ) } );
    }
    
  }

  /**
   * Adds the listeners for the map's events.
   *
   * @return {void}
   */
  setupMapEvents() {
    const { onChange } = this.props;

    // Fix map and popup view on Carbon Fields view change (e.g. when clicking through tabs in complex fields)
    const carbonBox = this.node.current.closest( '.postbox.carbon-box' );
    if ( carbonBox) {
      this.onClassChange( carbonBox, () => {
        this.map.invalidateSize();
      } );
    }
    const tabbedField = this.node.current.closest( '.cf-complex__group.cf-complex__group--tabbed' );
    if ( tabbedField ) {
      this.onHiddenToggle( tabbedField, () => {
        this.marker.closePopup();
        this.marker.openPopup();
        this.map.setView( this.marker.getLatLng() ).panBy([0,-50] );
      } )
    }
    // Get results from Leaflet GeoSearch
    this.map.addEventListener( 'geosearch/showlocation', ( result ) => {
      const r = result.location;

      // Create marker
      if( this.marker ){
        this.marker.remove();
      }
      this.marker = result.marker;
      this.marker.options.draggable = true;
      this.marker.addTo( this.map );
      this.marker.dragging.enable();

      const address = this.marker.getPopup().getContent();

      // Save location to database
      onChange( {
        lat: r.y,
        lng: r.x,
        address: address,
      } );

      // Adjust lat & lng on marker move
      this.marker.addEventListener( 'moveend', ( args ) => {
        const l = args.target.getLatLng();
        
        this.map.panTo( l );
        
        onChange( {
          lat: l.lat,
          lng: l.lng,
        } );
      } );
    } );
  }

  /**
   * Listen for class change
   */
  onClassChange( node, callback ) {
    let lastClassString = node.classList.toString();
  
    const mutationObserver = new MutationObserver( ( mutationList ) => {
      for ( const item of mutationList ) {
        if ( item.attributeName === 'class' ) {
          const classString = node.classList.toString();
          if ( classString !== lastClassString ) {
            callback( mutationObserver );
            lastClassString = classString;
            break;
          }
        }
      }
    });
  
    mutationObserver.observe( node, { attributes: true } );
  
    return mutationObserver;
  }

  /**
   * Listen for add/remove attribute "hidden"
   */
  onHiddenToggle( node, callback ) {
    let lastHiddenState = node.hasAttribute('hidden');
  
    const mutationObserver = new MutationObserver( ( mutationList ) => {
      for ( const item of mutationList ) {
        if ( item.attributeName === 'hidden' ) {
          const hiddenState = node.hasAttribute('hidden');
          if ( hiddenState !== lastHiddenState ) {
            callback( mutationObserver );
            lastHiddenState = hiddenState;
            break;
          }
        }
      }
    });
  
    mutationObserver.observe( node, { attributes: true } );
  
    return mutationObserver;
  }  

  /**
   * Render a Leaflet Map field.
   *
   * @return {Object}
   */
  render() {
    return <div className={ this.props.className } ref={ this.node }></div>;
  }
}

export default MapField;
