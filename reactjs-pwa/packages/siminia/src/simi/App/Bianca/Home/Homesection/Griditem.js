import React from 'react';
import PropTypes from 'prop-types';
import ReactHTMLParse from 'react-html-parser'
import Price from 'src/simi/App/Bianca/BaseComponents/Price';
import { prepareProduct } from 'src/simi/Helper/Product'
import { analyticClickGTM } from 'src/simi/Helper/Analytics'
import { Link } from 'src/drivers';
import LazyLoad from 'react-lazyload';
import { logoUrl } from 'src/simi/Helper/Url'
import Image from 'src/simi/BaseComponents/Image'
// import {StaticRate} from 'src/simi/BaseComponents/Rate'
import Identify from 'src/simi/Helper/Identify'
import { productUrlSuffix, saveDataToUrl } from 'src/simi/Helper/Url';
// import { Colorbtn } from 'src/simi/BaseComponents/Button'
// import QuickView from 'src/simi/App/Bianca/BaseComponents/QuickView';
// import { addToWishlist as simiAddToWishlist } from 'src/simi/Model/Wishlist';
// import { Util } from '@magento/peregrine';
// const { BrowserPersistence } = Util;
// import {showToastMessage} from 'src/simi/Helper/Message';
// import { showFogLoading, hideFogLoading } from 'src/simi/BaseComponents/Loading/GlobalLoading';
// import { addToCart as simiAddToCart } from 'src/simi/Model/Cart';
// import { getProductDetail } from 'src/simi/Model/Product';
import { withRouter } from 'react-router-dom';
// import { getOS } from 'src/simi/App/Bianca/Helper';
// import { connect } from 'src/drivers';
import { compose } from 'redux';
// import { smoothScrollToView } from 'src/simi/Helper/Behavior';

const $ = window.$;
require('./griditem.scss')


class Griditem extends React.Component {
    constructor(props) {
        super(props)
        const isPhone = window.innerWidth < 1024 
        this.state = ({
            openModal : false,
            isPhone: isPhone,
        })
        this.vendorName = ''
        this.setIsPhone()
    }

    setIsPhone(){
        const obj = this;
        $(window).resize(function () {
            const width = window.innerWidth;
            const isPhone = width < 1024;
            if(obj.state.isPhone !== isPhone){
                obj.setState({isPhone})
            }
        })
    }

    // addToCart = (pre_order = false) => {
    //     const {item} = this.props
    //     if (item && item.simiExtraField && item.simiExtraField.attribute_values) {
    //         const {attribute_values} = item.simiExtraField
    //         if ((!parseInt(attribute_values.has_options)) && attribute_values.type_id === 'simple') {
    //             const params = {product: String(item.id), qty: '1'}
    //             if (pre_order)
    //                 params.pre_order = 1
    //             showFogLoading()
    //             simiAddToCart(this.addToCartCallBack, params)
    //             return
    //         }
    //     }
    //     const { url_key } = item
    //     const { history } = this.props
    //     const product_url = `/${url_key}${productUrlSuffix()}`
    //     history.push(product_url)
    // }

    // smootScollToMain = () => {
    //     smoothScrollToView($('#siminia-main-page'));
    // }
    
    render() {
        const { props } = this
        const item = prepareProduct(props.item)
        const logo_url = logoUrl()
        if (!item) return '';
        const { name, url_key, id, price, type_id, small_image } = item
        const product_url = `/${url_key}${productUrlSuffix()}`
        saveDataToUrl(product_url, item)
        const location = {
            pathname: product_url,
            state: {
                product_id: id,
                item_data: item
            },
        }

        const image = (
            <div className="siminia-product-image" style={{backgroundColor: 'white'}} >
                <div style={{ position: 'absolute', left: 0, top: 0, bottom: 0, width: '100%' }}>
                    <Link to={location}>
                        {<Image className={`img-${id}`} src={small_image && small_image.url ? small_image.url : small_image} alt={name} />}
                    </Link>
                </div>
            </div>
        )
        
        return (
            <div className="siminia-product-grid-item" onClick={() => {analyticClickGTM(name, item.id, item.price)}}>
                <div style={{position: 'relative'}} className="grid-item-image">
                    {
                        props.lazyImage && false ?
                            (<LazyLoad placeholder={<img alt={name} src={logo_url} style={{ maxWidth: 60, maxHeight: 60 }} />}>
                                {image}
                            </LazyLoad>) : image
                    }
                </div>
                
                <div className="siminia-product-des">
                    <div className="product-des-info">
                        <div className="product-name">
                            <div role="presentation" className="product-name small"
                                onClick={() => {props.handleLink(location)}} >{ReactHTMLParse(name)}</div>
                        </div>
                        <div className="vendor-and-price">
                            <div role="presentation" className={`prices-layout ${Identify.isRtl() ? "prices-layout-rtl" : ''}`} id={`price-${id}`} 
                                onClick={() => {props.handleLink(location)}}>
                                <Price
                                    prices={price} type={type_id}
                                />
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        )
    }
}

Griditem.contextTypes = {
    item: PropTypes.object,
    handleLink: PropTypes.func,
    classes: PropTypes.object,
    lazyImage: PropTypes.bool,
}

export default compose(withRouter)(Griditem);
