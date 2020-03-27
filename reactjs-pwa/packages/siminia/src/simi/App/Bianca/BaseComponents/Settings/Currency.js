import React from 'react';
import StoreView from './Storeview';
import Identify from 'src/simi/Helper/Identify';
import CacheHelper from 'src/simi/Helper/CacheHelper';
import Check from 'src/simi/BaseComponents/Icon/TapitaIcons/SingleSelect';
import ListItemNested from 'src/simi/App/Bianca/BaseComponents/MuiListItem/Nested';
import {configColor} from 'src/simi/Config';
import MenuItem from "src/simi/App/Bianca/BaseComponents/MenuItem";
import {showFogLoading} from 'src/simi/BaseComponents/Loading/GlobalLoading'

import {saveCategoriesToDict} from 'src/simi/Helper/Url';
import simiStoreConfigDataQuery from 'src/simi/queries/getStoreConfigData.graphql';
import { Simiquery } from 'src/simi/Network/Query';
import  * as Constants from 'src/simi/Config/Constants'

import { Util } from '@magento/peregrine';
const { BrowserPersistence } = Util;
const storage = new BrowserPersistence();

class Currency extends StoreView {

    state = {
        changingCurrency: false,
        merchantConfigs: {}
    }

    constructor(props){
        super(props);
        this.checkCurrency = false;
    }
    
    selectCurrency(currency) {
        showFogLoading()
        const merchantConfigsBefore = Identify.getStoreConfig();
        let appSettings = Identify.getAppSettings()
        const cartId = storage.getItem('cartId')
        const signin_token = storage.getItem('signin_token')
        const simiSessId = Identify.getDataFromStoreage(Identify.LOCAL_STOREAGE, Constants.SIMI_SESS_ID)
        appSettings = appSettings?appSettings:{}
        const currentStoreId = parseInt(appSettings.store_id, 10);
        CacheHelper.clearCaches()
        appSettings.currency = currency;
        if (currentStoreId)
            appSettings.store_id = currentStoreId
        if (cartId)
            storage.setItem('cartId', cartId)
        if (signin_token)
            storage.setItem('signin_token', signin_token)
        Identify.storeDataToStoreage(Identify.LOCAL_STOREAGE, Constants.SIMI_SESS_ID, simiSessId)
        Identify.storeAppSettings(appSettings);
        // window.location.reload()
        this.setState({changingCurrency: true, merchantConfigs: merchantConfigsBefore})
    }

    getSelectedCurrency() {
        if (!this.selectedCurrency) {
            const merchantConfigs = Identify.getStoreConfig();
            if (merchantConfigs && merchantConfigs.simiStoreConfig)
                this.selectedCurrency = merchantConfigs.simiStoreConfig.currency
            if (!this.selectedCurrency) {
                const {currency} = Identify.getAppSettings() || {};
                this.selectedCurrency = currency;
            }
        }
        return this.selectedCurrency
    }

    renderItem() {
        const {store_id, currency} = Identify.getAppSettings() || {};
        if (this.state.changingCurrency) {
            return (
                <Simiquery query={simiStoreConfigDataQuery} variables={{storeId: store_id, currency}}>
                    {({ data }) => {
                        if (data && data.storeConfig) {
                            Identify.saveStoreConfig(data)
                            saveCategoriesToDict(data.simiRootCate)
                            window.location.reload()
                        } else if(data) {
                            window.location.reload()
                        }
                        return null
                    }}
                </Simiquery>
            )
        }
        try {
            if (typeof(storage) !== "undefined") {
                const merchantConfigs = Identify.getStoreConfig();
                if (!merchantConfigs) merchantConfigs = this.state.merchantConfigs;
                const currencies = merchantConfigs.simiStoreConfig.config.base.currencies;
                
                if(currencies.length > 1) {
                    this.checkCurrency = true;
                    return(
                        <div
                            className={this.props.className}
                        >
                            <ListItemNested
                                primarytext={<div className={`menu-title`} >{Identify.__('Currency')}</div>} 
                                >
                                {this.renderSubItem()}
                            </ListItemNested>
                        </div>
                    )
                }
            }
        } catch (err) {
            
        }
        return null;
    }

    renderSubItem(){
        if(!this.checkCurrency) return;
        let storesRender = [];
        let merchantConfigs = Identify.getStoreConfig();
        if (!merchantConfigs) merchantConfigs = this.state.merchantConfigs;
        const currencyList = merchantConfigs.simiStoreConfig.config.base.currencies || null

        
        if (currencyList !== null) {
            storesRender = currencyList.map((currency) => {
                const isSelected = currency.value === this.getSelectedCurrency();
                const icon = isSelected ? 
                    <Check color={configColor.button_background} style={{width: 18, height: 18}} /> : 
                    <span className={`not-selected`} style={{width: 18, height: 18}}></span>;
                const currencyItem = (
                    <span className={`currency-item`} style={{display: 'flex'}}>
                        <div className={`currency-name`}>
                            {currency.title}
                        </div>
                    </span>
                )
                if (isSelected) {
                    return null;
                }
                return (
                    <div 
                        role="presentation"
                        key={Identify.randomString(5)}
                        style={{marginLeft: 5,marginRight:5}}
                        onClick={() => this.selectCurrency(currency.value)}>
                            <MenuItem title={currencyItem} className={this.props.className}/>
                    </div>
                );
            });
            return storesRender;
        }
    }

    render(){
        try {
            const item = this.renderItem()
            return item
        } catch(err) {
            console.log(err)
        }
        return ''
    }
}
export default Currency;