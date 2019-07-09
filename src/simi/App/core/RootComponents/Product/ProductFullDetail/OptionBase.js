import React from 'react';
import {Qty} from 'src/simi/BaseComponents/Input/index';
import Identify from "src/simi/Helper/Identify";
import {showToastMessage} from 'src/simi/Helper/Message';
import {formatPrice} from "src/simi/Helper/Pricing";
import {configColor} from "src/simi/Config";

class OptionBase extends React.Component {
    constructor(props) {
        super(props);
        this.data = this.props.app_options;
        this.parentObj = this.props.parent;
        this.selected = {};
        this.required = [];
        this.params = {
            'product' : this.props.product_id
        };
        this.PriceComponent = this.parentObj.Price;
    }

    renderOptions =()=>{
        return <div></div>;
    };

    renderOptionInputQty = (key,qty = 1,style={}) => {
        return <Qty id={key} className={`option-qty option-qty-${key}`} value={qty} inputStyle={style}/>;
    };

    renderOptionPrice = (price) => {
        return <span className="price" style={{color : configColor.price_color,marginLeft:0,fontSize : 16}}>{formatPrice(price)}</span>
    };

    renderLabelRequired = (show = 1) => {
        if(show === 1){
            return <span className="required-label" style={{marginLeft : '5px', color : '#ff0000'}}>*</span>;
        }
        return null;
    };

    renderLabelOption = (title,price)=>{
        const symbol = price > 0 ? <span style={{margin:'0 10px'}}>+</span> : null;
        price = price > 0 ? this.renderOptionPrice(price) : null;
        const label  = <div style={{display : 'flex',fontWeight: '400'}} className={`label-option-text`}>
                        <span style={{
                            fontSize: '16px',
                        }}>1 x {title}</span>
                        {symbol}
                        {price}
                    </div>;
        return label;
    };

    getStatePrice =(PriceComponent = this.parentObj.Price)=>{
        return PriceComponent.state.prices;
    };

    updateStatePrice = (prices = this.getStatePrice(),PriceComponent = this.parentObj.Price)=>{
        PriceComponent.updatePrices(prices);
    };

    updatePrices=(selected = this.selected) =>{
        console.log(selected)
        return <div></div>;
    };

    updateOptions =(key,val)=>{
        this.selected[key] = val;
        this.updatePrices();
    };

    deleteOptions =(key)=>{
        if(this.selected[key]){
            delete this.selected[key];
            this.updatePrices();
        }
    }

    setParamOption = (keyOption = null) => {
        if(keyOption === null) return ;
        this.params[keyOption] = this.selected;
    };

    getProductType = () => {
        return this.props.parent.product_type;
    };

    setParamQty =(keyQty = null)=>{
        if(keyQty === null) return ;
        const obj = this;
        const json = {};
        const qty = $('input.option-qty');
        qty.each(function () {
            const val = $(this).val();
            const id = $(this).attr('data-id');
            if(obj.selected[id]){
                json[id] = val;
            }
        });
        this.params[keyQty] = json;
    };

    /*
    *  get Params Add to Cart
    * */
    getParams = () => {
        this.setParamQty('qty');
        return this.params;
    };

    handleChangeQty = ()=>{
        const obj = this;
        $(function () {
            $('input.option-qty').change(function () {
                obj.updatePrices();
            })
        })
    }

    checkOptionRequired =(selected = this.selected,required=this.required)=>{
        let check = true;
        for (const i in required){
            const requiredOptionId = required[i];
            if(!selected.hasOwnProperty(requiredOptionId) || !selected[requiredOptionId] || selected[requiredOptionId].length === 0){
                check = false;
                break;
            }
        }
        if(!check){
            showToastMessage(Identify.__('Please select the options required (*)'));
        }
        return check;
    }
}

export default OptionBase;