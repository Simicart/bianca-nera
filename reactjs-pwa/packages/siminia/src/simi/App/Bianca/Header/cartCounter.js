import React, { Component } from 'react';
import classify from 'src/classify';
import PropTypes from 'prop-types';

import defaultClasses from './cartCounter.css';

class CartCounter extends Component {
    static propTypes = {
        classes: PropTypes.shape({
            root: PropTypes.string
        }),
        counter: PropTypes.number.isRequired
    };

    caculatorCounter = (attribute) => {
        const {totals} = this.props;
        let qty = 0
        if(totals && totals.items && totals.items.length) {
            totals.items.forEach((item) => {
                if(item[attribute]) {
                    const options = JSON.parse(item[attribute])
                    options.forEach((option) => {
                        qty += option.quantity
                    })
                    
                }
            })
        }

        return qty
    }

    render() {
        const { counter, classes } = this.props;
        const tryToBuyQty = this.caculatorCounter('simi_trytobuy_option');
        const preOrderQty = this.caculatorCounter('simi_pre_order_option');
        let totalQty = counter
        if(tryToBuyQty > 0) {
            totalQty = tryToBuyQty
        } else if(preOrderQty > 0) {
            totalQty = preOrderQty
        }
        return totalQty > 0 ? (
            <span className={classes.root}><span>{totalQty}</span></span>
        ) : null;
    }
}

export default classify(defaultClasses)(CartCounter);
