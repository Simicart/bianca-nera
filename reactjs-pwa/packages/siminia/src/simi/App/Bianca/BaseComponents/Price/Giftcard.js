import React from 'react';
import Abstract from './Abstract';
import Identify from 'src/simi/Helper/Identify'

class Giftcard extends Abstract {
    renderView = () => {
        const choosedPrice = this.parent && this.parent.props.choosedPrice || false;

        const priceVal = this.prices.minimalPrice.amount.value;
        const currency = this.prices.minimalPrice.amount.currency;

        const price = (
            choosedPrice ?
            <div className="regular">
                {this.formatPrice(priceVal, currency)}
            </div>
            :
            <div className="regular">
                {Identify.__('From ')} {this.formatPrice(priceVal, currency)}
            </div>
        );
        return (
            <div className='product-prices'>
                {price}
            </div>
        );
    };

    render(){
        return super.render();
    }
}
export default Giftcard;