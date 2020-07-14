import React, { Component } from 'react';
import { arrayOf, func, shape, string } from 'prop-types';

import Option from './option';

class Options extends Component {
    static propTypes = {
        onSelectionChange: func,
        options: arrayOf(
            shape({
                attribute_id: string.isRequired
            })
        ).isRequired
    };

    handleSelectionChange = (optionId, selection) => {
        const { onSelectionChange, optionSelections, options } = this.props;
        // reset all selected option when change option is size
        /**
        const option = options.find((opt) => {
            return opt.attribute_id === optionId;
        })
        if (option && option.attribute_code === 'size') {
            if (optionSelections instanceof Map && optionSelections.size) {
                optionSelections.forEach((value_index, attribute_id) => {
                    if (optionId !== attribute_id) optionSelections.delete(attribute_id)
                })
            }
        }
        */
        if (onSelectionChange) {
            onSelectionChange(optionId, selection);
        }
    };

    handleAskOption = (optionId, code) => {
        const {onSizeGuideClick} = this.props;
        if (code === 'size' && onSizeGuideClick && typeof onSizeGuideClick === 'function') {
            onSizeGuideClick(optionId, code);
        }
    }

    mapOptionInStock = () => {
        const { options, optionsIndex, variants, optionSelections } = this.props;
        let instockColors = [];
        
        if (optionSelections instanceof Map && optionSelections.size) {
            const colorOption = options.find((opt) => {
                return opt.attribute_code === 'color';
            });
            // Show colors is in stock only (Hide the color is out of stock)
            colorOption && optionSelections.forEach((selected_value_index, key) => {
                for (let i in optionsIndex) {
                    if (optionsIndex[i]['code'] !== 'color' && optionsIndex[i]['options'] && optionsIndex[i]['options'].length){
                        optionsIndex[i]['options'].forEach((option) => {
                            if(parseInt(option.id) === selected_value_index){
                                if (option.products && option.products.length) {
                                    if (optionsIndex[colorOption.attribute_id] && optionsIndex[colorOption.attribute_id]['options']
                                        && optionsIndex[colorOption.attribute_id]['options'].length){
                                            optionsIndex[colorOption.attribute_id]['options'].forEach((optionColor) => {
                                                if(optionColor.products && optionColor.products.length){
                                                    optionColor.products.forEach((productId) => {
                                                        if (option.products.includes(productId)) {
                                                            instockColors.push(parseInt(optionColor.id)); // add value_index of color option to array
                                                        }
                                                    });
                                                }
                                            });
                                    }
                                }
                            }
                        });
                    }
                }
            });
            
            if (colorOption) {
                optionSelections.forEach((value_index, attribute_id) => {
                    if (colorOption.attribute_id === attribute_id && instockColors.length && !instockColors.includes(value_index)){
                        optionSelections.delete(attribute_id)
                    }
                })
            }
        }
        return options.map(option => {
            //filter option values for color
            let _option = {...option}
            if(_option.attribute_code === 'color' && instockColors.length){
                _option.values = _option.values.filter((value) => instockColors.includes(value.value_index));
            }
            //mapping product variants in stock to options
            /* if(_option.values && _option.values instanceof Array){
                _option.values.forEach((optionVal) => {
                    let products = [];
                    variants.forEach((variant) => {
                        if (variant.attributes){
                            let variantAttributes = variant.attributes.find((item) => {
                                    return (item.code === _option.attribute_code && parseInt(item.value_index) === parseInt(optionVal.value_index))
                                });
                            if (variantAttributes && variant.product && variant.product.stock_status === 'IN_STOCK') {
                                    products.push(variant.product);
                            }
                        }
                    });
                    optionVal.products = products;
                });
            } */
            return _option;
        });
    }

    render() {
        const { handleSelectionChange, handleAskOption } = this;
        // const { optionSelections } = this.props;
        const options = this.mapOptionInStock(); //implement out-of-stock for color option
        return options.map(option => (
            <Option
                {...option}
                key={option.attribute_id}
                onSelectionChange={handleSelectionChange}
                onAskOption={handleAskOption}
            />
        ));
    }
}

export default Options;