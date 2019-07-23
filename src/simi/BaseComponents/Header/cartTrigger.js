import React, { Component } from 'react';
import { connect } from 'src/drivers';
import { compose } from 'redux';
import PropTypes from 'prop-types';

import { toggleCart, createCart } from 'src/actions/cart';
import CartCounter from './cartCounter';

import Basket from "src/simi/BaseComponents/Icon/Basket";
import classify from 'src/classify';
import defaultClasses from './cartTrigger.css'
import Identify from 'src/simi/Helper/Identify'
import { Link } from 'src/drivers'
import { resourceUrl } from 'src/simi/Helper/Url'

export class Trigger extends Component {
    constructor(props) {
        super(props)
        //props.createCart() //want to init cart at first, uncomment this
    }

    static propTypes = {
        children: PropTypes.node,
        classes: PropTypes.shape({
            root: PropTypes.string
        }),
        createCart: PropTypes.func.isRequired,
        toggleCart: PropTypes.func.isRequired,
        itemsQty: PropTypes.number
    };

    get cartIcon() {
        const {
            classes,
            cart: { details }
        } = this.props;
        const itemsQty = details.items_qty;
        const iconColor = 'rgb(var(--venia-text))';
        const svgAttributes = {
            stroke: iconColor
        };

        if (itemsQty > 0) {
            svgAttributes.fill = iconColor;
        }
        return (
            <React.Fragment>
                <div className={classes['item-icon']} style={{display: 'flex', justifyContent: 'center'}}>  
                    <Basket style={{width: 30, height: 30, display: 'block', margin: 0}}/>
                </div>
                <div className={classes['item-text']}>
                    {Identify.__('Basket')}
                </div>
            </React.Fragment>
        )
    }

    render() {
        const {
            classes,
            //toggleCart,
            cart: { details }
        } = this.props;
        const { cartIcon } = this;
        const itemsQty = details.items_qty;
        return (
            <Link 
                to={resourceUrl('/cart.html')}
                className={classes.root}
                aria-label="Open cart page"
            >
                {cartIcon}
                <CartCounter counter={itemsQty ? itemsQty : 0} />
            </Link>
        )
        /*
        return (
            <button
                className={classes.root}
                aria-label="Toggle mini cart"
                onClick={toggleCart}
            >
                {cartIcon}
                <CartCounter counter={itemsQty ? itemsQty : 0} />
            </button>
        );
        */
    }
}

const mapStateToProps = ({ cart }) => ({ cart });

const mapDispatchToProps = {
    toggleCart,
    createCart
};

export default compose(
    classify(defaultClasses),
    connect(
        mapStateToProps,
        mapDispatchToProps
    )
)(Trigger);
