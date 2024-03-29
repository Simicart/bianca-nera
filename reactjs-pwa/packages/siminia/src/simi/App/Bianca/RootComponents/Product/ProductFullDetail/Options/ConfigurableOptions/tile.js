import React, { Component } from 'react';
import { bool, number, shape, string } from 'prop-types';

require('./tile.scss');

const getClassName = (name, isSelected, hasFocus) =>
	`${name} ${isSelected ? '_selected' : ''} ${hasFocus ? '_focused' : ''}`;

class Tile extends Component {

	constructor(props){
		super(props)
	}

	static propTypes = {
		hasFocus: bool,
		isSelected: bool,
		item: shape({
			label: string.isRequired
		}).isRequired,
		itemIndex: number
	};

	static defaultProps = {
		hasFocus: false,
		isSelected: false
    };

	render() {
		const {
			hasFocus,
			isSelected,
			item,
			// eslint-disable-next-line
			itemIndex,
			...restProps
		} = this.props;
		const className = getClassName('tile-root', isSelected, hasFocus);
		const { option_value, label } = item;
		if (option_value) {
			if (option_value.includes('http')) {
				return (
					<div className="group-options">
						<button {...restProps} className={className}>
							<img src={option_value} alt={label}/>
						</button>
					</div>
				);
			}
			return (
				<div className="group-options">
					<button {...restProps} className={className} style={{backgroundColor: option_value}}>
					</button>
				</div>
			);
		}
		if (label === 'false') return null;
		return (
			<div className="group-options">
				<button {...restProps} className={className}>
					<span>{label}</span>
				</button>
			</div>
		);
	}
}

export default Tile;
