import React from 'react';
import Identify from 'src/simi/Helper/Identify';
import Price from 'src/simi/App/Bianca/BaseComponents/Price';
import ObjectHelper from 'src/simi/Helper/ObjectHelper';
import PropTypes from 'prop-types';

require('./productprice.scss');

const initState = {
    customPrice: {},
    customOptionPrice: {exclT:0, inclT:0},
    downloadableOptionPrice: {exclT:0, inclT:0},
    choosedPrice: false
}

class ProductPrice extends React.Component {

    constructor(props){
        super(props);
        const {configurableOptionSelection} = props
        this.state = {...initState, ...{sltdConfigOption: ObjectHelper.mapToObject(configurableOptionSelection)}};
    }
    
    setCustomPrice(exclT, inclT) {
        this.setState({
            customPrice: {exclT, inclT},
            choosedPrice: true
        })
    }

    setCustomOptionPrice(exclT, inclT) {
        this.setState({
            customOptionPrice: {exclT, inclT},
            choosedPrice: true
        })
    }
    setDownloadableOptionPrice(exclT, inclT) {
        this.setState({
            downloadableOptionPrice: {exclT, inclT},
            choosedPrice: true
        })
    }

    static getDerivedStateFromProps(nextProps, prevState) {
        const {configurableOptionSelection} = nextProps
        const {sltdConfigOption} = prevState
        const newSltdConfigOption = ObjectHelper.mapToObject(configurableOptionSelection)
        if (!ObjectHelper.shallowEqual(sltdConfigOption, newSltdConfigOption))
            return {...initState, ...{sltdConfigOption: newSltdConfigOption}}
        return {}
    }

    calcConfigurablePrice = (price) => {
        // const {sltdConfigOption} = this.state
        const {data, configurableOptionSelection: optionSelection} = this.props
        const {simiExtraField} = data || {}
        const {app_options} = simiExtraField || {}
        const {configurable_options: options} = app_options || {}
        const {attributes, optionPrices} = options || {}

        if (optionSelection && attributes && optionPrices) {
            let products = []; // product id array
            // get sub product id
            for(let attrId in attributes){
                const {options} = attributes[attrId] || {};
                options && options.every((opt) => {
                    // compare id of option value selected
                    if (parseInt(opt.id) === optionSelection.get(attrId)){
                        if (!products.length) {
                            products = opt.products; // add products of option in first attribute
                        } else {
                            if (products.length && opt.products.length) {
                                let _products = [];
                                products.every((productId) => {
                                    if (opt.products.includes(productId)) {
                                        _products.push(productId);
                                        return false;
                                    }
                                    return true;
                                });
                                products = _products; // replace the first products with new matched products
                            }
                        }
                        return false; // break
                    }
                    return true; // continue
                });
            }
            let sub_product_id = '';
            if (products.length) {
                sub_product_id = products[0]; // get first product id in array
            }
            /* for (const index_id in options.index) {
                const index = options.index[index_id] 
                if (ObjectHelper.shallowEqual(index, sltdConfigOption)) {
                    sub_product_id = index_id;
                    break;
                }
            } */
            if (sub_product_id) {
                let sub_product_price = optionPrices[sub_product_id]
                if (!sub_product_price)
                    sub_product_price = optionPrices[parseInt(sub_product_id, 10)]
                if (sub_product_price) {
                    price.minimalPrice.excl_tax_amount.value = sub_product_price.basePrice.amount
                    price.minimalPrice.amount.value = sub_product_price.finalPrice.amount
                    price.regularPrice.excl_tax_amount.value = sub_product_price.basePrice.amount
                    price.regularPrice.amount.value = sub_product_price.finalPrice.amount
                    price.maximalPrice.excl_tax_amount.value = sub_product_price.basePrice.amount
                    price.maximalPrice.amount.value = sub_product_price.finalPrice.amount
                }
            }
        }
    }

    addOptionPrice(calculatedPrices, optionPrice) {
        calculatedPrices.minimalPrice.excl_tax_amount.value += optionPrice.exclT;
        calculatedPrices.minimalPrice.amount.value += optionPrice.inclT;
        calculatedPrices.regularPrice.excl_tax_amount.value += optionPrice.exclT;
        calculatedPrices.regularPrice.amount.value += optionPrice.inclT;
        calculatedPrices.maximalPrice.excl_tax_amount.value += optionPrice.exclT;
        calculatedPrices.maximalPrice.amount.value += optionPrice.inclT;
    }

    calcPrices(price) {
        const {customOptionPrice, downloadableOptionPrice} = this.state
        let calculatedPrices = JSON.parse(JSON.stringify(price))
        const {data} = this.props
        if (data.type_id === 'configurable')
            this.calcConfigurablePrice(calculatedPrices)
        
        // custom option
        this.addOptionPrice(calculatedPrices, customOptionPrice)

        // downloadable option
        if (data.type_id === 'downloadable')
            this.addOptionPrice(calculatedPrices, downloadableOptionPrice)

        // if set custom price
        if (this.state.customPrice.exclT) {
            calculatedPrices.minimalPrice.excl_tax_amount.value = this.state.customPrice.exclT
            calculatedPrices.regularPrice.excl_tax_amount.value = this.state.customPrice.exclT
        }
        if (this.state.customPrice.inclT) {
            calculatedPrices.minimalPrice.amount.value = this.state.customPrice.inclT
            calculatedPrices.regularPrice.amount.value = this.state.customPrice.inclT
        }
        
        return calculatedPrices
    }

    render(){
        const {data} = this.props
        const {simiExtraField} = data
        const prices = this.calcPrices(data.price)
        let stockLabel = ''
        if (simiExtraField) {
            if (parseInt(simiExtraField.attribute_values.is_salable, 10) !== 1)
                stockLabel = Identify.__('Out of stock');
            else 
                stockLabel = Identify.__('In stock');
        }
                
        const priceLabel = (
            <div className='prices-layout'>
                {
                    (data.type_id !== "grouped") &&
                    <Price config={1} key={Identify.randomString(5)} prices={prices} type={data.type_id} choosedPrice={this.state.choosedPrice}/>
                }
            </div>
        );
        return (
            <div className='prices-container' id={data.type_id}>
                {priceLabel}
                {/* <div className='product-stock-status'>
                    <div className='stock-status'>
                        {stockLabel}
                    </div>
                    {
                        data.sku && 
                        <div className={`product-sku flex`} id="product-sku">
                            <span className='sku-label'>{Identify.__('Sku') + ": "} {data.sku}</span>
                        </div>
                    }
                </div> */}
            </div>

        );
    }
}

ProductPrice.propTypes = {
    data: PropTypes.object.isRequired,
    configurableOptionSelection: PropTypes.instanceOf(Map)
}
ProductPrice.defaultProps = {
    configurableOptionSelection: new Map(),
}

export default ProductPrice;