import React, { Component } from 'react';
import { string, shape } from 'prop-types';
import Identify from 'src/simi/Helper/Identify';
import classify from 'src/classify';
import Trigger from './trigger';
import defaultClasses from './emptyMiniCart.css';
import BasketIcon from '../../BaseComponents/Icon/Basket';

class EmptyMiniCart extends Component {
    static propTypes = {
        classes: shape({
            root: string,
            emptyTitle: string,
            continue: string
        })
    };

    render() {
        const { classes } = this.props;

        return (
            <div className={`${classes.root} empty-mobile`}>
                <BasketIcon style={{height: '30px', width: '30px'}}/>
                <h3 className={classes.emptyTitle}>
                    {Identify.__('YOUR CART IS EMPTY')}
                </h3>
                <Trigger>
                    <span className={classes.continue}>{Identify.__('Continue Shopping')}</span>
                </Trigger>
            </div>
        );
    }
}

export default classify(defaultClasses)(EmptyMiniCart);
