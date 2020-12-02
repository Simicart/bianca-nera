import React, { Component } from 'react';
import { string, shape } from 'prop-types';
import Identify from 'src/simi/Helper/Identify';
import classify from 'src/classify';
import defaultClasses from './emptyMiniCart.css';
import BasketIcon from '../../BaseComponents/Icon/Basket';
import { compose } from 'redux';
import { withRouter } from 'react-router-dom';

class EmptyMiniCart extends Component {
    static propTypes = {
        classes: shape({
            root: string,
            emptyTitle: string,
            continue: string
        })
    };

    continueShopping = () => {
        const {history, closeDrawer} = this.props
        // history && history.push('/');
        if(closeDrawer) {
            closeDrawer()
            document.body.classList.remove('minicart-open');
        }
    }

    render() {
        const { classes } = this.props;

        return (
            <div className={`${classes.root} empty-mobile`}>
                <BasketIcon style={{height: '30px', width: '30px'}}/>
                <h3 className={classes.emptyTitle}>
                    {Identify.__('YOUR CART IS EMPTY')}
                </h3>
                <button onClick={this.continueShopping}>
                    <span className={classes.continue}>{Identify.__('Continue Shopping')}</span>
                </button>
            </div>
        );
    }
}

export default compose(classify(defaultClasses), withRouter)(EmptyMiniCart);
