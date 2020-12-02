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

    selectedValueIndex = -1;

    handleSelectionChange = (optionId, selection) => {
        const { onSelectionChange } = this.props;
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
        const { options, optionsIndex, variants, optionSelections, pre_order } = this.props;
        // let instockColors = [];
        let instockSizes = [];
        
        if (optionSelections instanceof Map && optionSelections.size) {
            /* const colorOption = options.find((opt) => {
                return opt.attribute_code === 'color';
            }); */
            const sizeOption = options.find((opt) => {
                return opt.attribute_code === 'size';
            });
            // Show colors is in stock only (Hide the color is out of stock)
            /* colorOption && optionSelections.forEach((selected_value_index, key) => {
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
            }); */
            // Show size is in stock only (Hide the size is out of stock)
            sizeOption && optionSelections.forEach((selected_value_index, key) => {
                for (let i in optionsIndex) {
                    if (optionsIndex[i]['code'] !== 'size' && optionsIndex[i]['options'] && optionsIndex[i]['options'].length){
                        optionsIndex[i]['options'].every((option) => {
                            if(parseInt(option.id) === selected_value_index){
                                if (option.products && option.products.length) {
                                    if (optionsIndex[sizeOption.attribute_id] && optionsIndex[sizeOption.attribute_id]['options']
                                        && optionsIndex[sizeOption.attribute_id]['options'].length){
                                            optionsIndex[sizeOption.attribute_id]['options'].forEach((optionIndex) => {
                                                if(optionIndex.products && optionIndex.products.length){
                                                    optionIndex.products.forEach((productId) => {
                                                        if (option.products.includes(productId)) {
                                                            instockSizes.push(parseInt(optionIndex.id)); // add value_index of size option to array
                                                        }
                                                    });
                                                }
                                            });
                                    }
                                }
                                return false;
                            }
                            return true;
                        });
                    }
                }
            });
            
            // Delete color selected in optionSelection (if not found)
            /* if (colorOption) {
                optionSelections.forEach((value_index, attribute_id) => {
                    if (colorOption.attribute_id === attribute_id && instockColors.length && !instockColors.includes(value_index)){
                        optionSelections.delete(attribute_id)
                    }
                })
            } */
            // Delete size selected in optionSelection (if not found)
            if (sizeOption) {
                optionSelections.forEach((value_index, attribute_id) => {
                    if (sizeOption.attribute_id === attribute_id && instockSizes.length && !instockSizes.includes(value_index)){
                        this.selectedValueIndex = value_index;
                        optionSelections.delete(attribute_id);
                    }
                })
            }
        }
        return options.map(option => {
            //filter option values for color
            let _option = {...option}
            /* if(_option.attribute_code === 'color' && instockColors.length){
                _option.values = _option.values.filter((value) => instockColors.includes(value.value_index));
            } */
            if(_option.attribute_code === 'size'){
                if (optionSelections instanceof Map && optionSelections.get(_option.attribute_id) && 
                    optionSelections.size < 2
                ) {
                    return _option; // fix bug
                }
                if (instockSizes.length) {
                    _option.values = _option.values.filter((value) => instockSizes.includes(value.value_index));
                } else if(optionSelections instanceof Map && optionSelections.size && pre_order && pre_order !== '1') {
                    _option.values = [];
                }
            }
            return _option;
        });
    }

    render() {
        const { handleSelectionChange, handleAskOption } = this;
        // const { optionSelections } = this.props;
        const options = this.mapOptionInStock(); //implement out-of-stock for color option
        return options.map(option => {
            return <Option
                {...option}
                key={`${option.attribute_id}-${this.selectedValueIndex}`}
                onSelectionChange={handleSelectionChange}
                onAskOption={handleAskOption}
            />
        });
    }
}

export default Options;