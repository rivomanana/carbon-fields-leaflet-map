/**
 * External dependencies.
 */
import { registerFieldType } from '@carbon-fields/core';

/**
 * Internal dependencies.
 */
import './style.scss';
import MapField from './MapField';

registerFieldType( 'leaflet_map', MapField );
