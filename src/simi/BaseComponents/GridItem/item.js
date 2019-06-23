import React from 'react';
import ObjectHelper from 'src/simi/Helper/ObjectHelper';
import defaultClasses from './item.css'
import {configColor} from 'src/simi/Config';
import PropTypes from 'prop-types';
import ReactHTMLParse from 'react-html-parser'
import { mergeClasses } from 'src/classify'
import { Price } from '@magento/peregrine'

const productUrlSuffix = '.html';

class Griditem extends React.Component {
    constructor(props, context) {
        super(props, context);
        this.item = this.props.item
    }

    shouldComponentUpdate(nextProps,nextState){
        if (!this.item)
            return ObjectHelper.shallowCompare(this,nextProps,nextState);
        else
            return (JSON.stringify(this.item) !== JSON.stringify(nextProps.item));
    }

    render() {
        const { classes } = this.props
        const { item } = this

        if (!item)
            return '';
        const itemClasses = mergeClasses(defaultClasses, classes);
        const { name, price, url_key, id, small_image } = item
        this.location = {
            pathname: `/${url_key}${productUrlSuffix}`,
            state: {
                product_id: id,
                item_data: item
            },
        }
        
        const image = (
            <div 
                role="presentation"
                className={itemClasses["siminia-product-image"]}
                onClick={()=>this.props.handleLink(this.location)}
                style={{borderColor: configColor.image_border_color,
                    backgroundColor: 'white'
                }}>
                <div style={{position:'absolute',top:0,bottom:0,width: '100%', padding: 1}}>
                    {<img src={small_image} alt={name}/>}
                </div>
            </div>
        )
        return (
            <div className={`${itemClasses["product-item"]} ${itemClasses["siminia-product-grid-item"]}`}>
                {
                    image
                }
                <div className={itemClasses["siminia-product-des"]}>
                    <div role="presentation" className={`${itemClasses["product-name"]} ${itemClasses["small"]}`} onClick={()=>this.props.handleLink(this.location)}>{ReactHTMLParse(name)}</div>
                    <div role="presentation" className={itemClasses["prices-layout"]} id={`price-${id}`} onClick={()=>this.props.handleLink(this.location)}>
                        <Price
                            value={price.regularPrice.amount.value}
                            currencyCode={price.regularPrice.amount.currency}
                        />
                    </div>
                </div>
            </div>
        );
    }
}

Griditem.contextTypes = {
    item: PropTypes.object,
    handleLink: PropTypes.func,
    classes: PropTypes.object,
}

export default Griditem;