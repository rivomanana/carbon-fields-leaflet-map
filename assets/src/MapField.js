/**
 * External dependencies.
 */
import { Component, Fragment } from '@wordpress/element';

/**
 * The internal dependencies.
 */
import LeafletMap from './LeafletMap';

class MapField extends Component {
  /**
	 * Handles the change of map location.
	 *
	 * @param  {Object} location
	 * @return {void}
	 */
	handleChange = ( location ) => {
		const {
			id,
			value,
			onChange
		} = this.props;

		onChange( id, {
			...value,
			...location
		} );
	}

	/**
	 * Render a number input field.
	 *
	 * @return {Object}
	 */
	render() {
		const {
			id,
			name,
			value,
			field
		} = this.props;

		return (
      <Fragment>
        <LeafletMap
          className="cf-leaflet-map__canvas"
          value={ value }
          field={ field }
					onChange={ this.handleChange }
        />

        <input
					type="hidden"
					name={ `${ name }[lat]` }
					value={ value.lat }
          readOnly
				/>

				<input
					type="hidden"
					name={ `${ name }[lng]` }
					value={ value.lng }
					readOnly
				/>

				<input
					type="hidden"
					name={ `${ name }[address]` }
					value={ value.address }
					readOnly
				/>
      </Fragment>
		);
	}
}

export default MapField;
