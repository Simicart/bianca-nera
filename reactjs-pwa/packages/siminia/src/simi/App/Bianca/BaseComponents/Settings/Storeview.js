import React from 'react';
import Identify from 'src/simi/Helper/Identify';
import CacheHelper from 'src/simi/Helper/CacheHelper';
import Check from 'src/simi/BaseComponents/Icon/TapitaIcons/SingleSelect';
import {configColor} from 'src/simi/Config';
import ListItemNested from 'src/simi/App/Bianca/BaseComponents/MuiListItem/Nested';
import MenuItem from 'src/simi/App/Bianca/BaseComponents/MenuItem'
import {showFogLoading} from 'src/simi/BaseComponents/Loading/GlobalLoading'
import  * as Constants from 'src/simi/Config/Constants'

import { Util } from '@magento/peregrine';
const { BrowserPersistence } = Util;
const storage = new BrowserPersistence();

import {saveCategoriesToDict} from 'src/simi/Helper/Url';
import simiStoreConfigDataQuery from 'src/simi/queries/getStoreConfigData.graphql';
import { Simiquery } from 'src/simi/Network/Query';

class Storeview extends React.Component {

    state = {
        changingStore: false,
        merchantConfigs: {}
    }

    constructor(props) {
        super(props);
        this.checkStore = false;
        this.selectedStoreId = false
        this.selectedGroupId = false
    }

    selectedStore(store) {
        showFogLoading()
        const merchantConfigsBefore = Identify.getStoreConfig();
        let appSettings = Identify.getAppSettings()
        const cartId = storage.getItem('cartId')
        const signin_token = storage.getItem('signin_token')
        const simiSessId = Identify.getDataFromStoreage(Identify.LOCAL_STOREAGE, Constants.SIMI_SESS_ID)
        appSettings = appSettings?appSettings:{}
        CacheHelper.clearCaches()
        appSettings.store_id = parseInt(store.store_id, 10);
        if (cartId)
            storage.setItem('cartId', cartId)
        if (signin_token)
            storage.setItem('signin_token', signin_token)
        Identify.storeDataToStoreage(Identify.LOCAL_STOREAGE, Constants.SIMI_SESS_ID, simiSessId)
        Identify.storeAppSettings(appSettings);
        // window.location.reload()
        this.setState({changingStore: true, merchantConfigs: merchantConfigsBefore})
    }

    getSelectedStoreId() {
        if (!this.selectedStoreId) {
            const merchantConfigs = Identify.getStoreConfig();
            if (merchantConfigs && merchantConfigs.storeConfig)
                this.selectedStoreId = parseInt(merchantConfigs.storeConfig.id, 10)
        }
        if (!this.selectedStoreId) {
            const {store_id} = Identify.getAppSettings() || {};
            this.selectedStoreId = parseInt(store_id, 10)
        }
        return this.selectedStoreId
    }

    getSelectedGroupId() {
        if (!this.selectedGroupId) {
            const merchantConfigs = Identify.getStoreConfig();
            if (merchantConfigs && merchantConfigs.simiStoreConfig)
                this.selectedGroupId = merchantConfigs.simiStoreConfig.config.base.group_id
        }
        return this.selectedGroupId
    }

    renderItem() {
        if (this.state.changingStore) {
            const {store_id, currency} = Identify.getAppSettings() || {};
            const cartId = storage.getItem('cartId')
            return (
                <Simiquery query={simiStoreConfigDataQuery} variables={{storeId: store_id, currency, cartId: cartId?cartId:0}}>
                    {({ loading, data, error }) => {
                        if (loading) return null;
                        if (error) window.location.reload();
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
        
        if (typeof(storage) !== "undefined") {
            try {
                const merchantConfigs = Identify.getStoreConfig();
                const storeList = merchantConfigs.simiStoreConfig.config.stores.stores;
                const selectedStore = storeList.filter((item) => {
                    return item.group_id === this.getSelectedGroupId()
                })[0];
                const storeViews = selectedStore.storeviews.storeviews;
                if (storeViews.length > 1) {
                    this.checkStore = true;
                    return(
                        <div className={this.props.className}>
                            <ListItemNested
                                primarytext={<div className={'menu-title'} >{Identify.__('Language')}</div>}
                                >
                                {this.renderSubItem(storeViews)}
                            </ListItemNested>
                        </div>
                    )
                }
            } catch (err) {
                
            }
        }
        return false;
    }

    renderSubItem(storeViews) {
        const {classes} = this.props
        if(!this.checkStore) return;
        let storesRender = [];
        
        storesRender = storeViews.map((store) => {
            if(parseInt(store.is_active,10) !== 1 ) return null;
            const isSelected = parseInt(store.store_id, 10) === this.getSelectedStoreId();
            const icon = isSelected ? 
                <Check color={configColor.button_background} style={{width: 18, height: 18}}/> : 
                <span className={`not-selected`} style={{width: 18, height: 18}}></span>;
            const storeItem =  (
                <div className={'store-item'} style={{display: 'flex'}}>
                    <div className={`store-name`}>
                        {Identify.__(store.name)}
                    </div>
                </div>
            )
            if (isSelected) {
                return null;
            }
            return (
                <div 
                    role="presentation"
                    key={Identify.randomString(5)}
                    style={{marginLeft: 5,marginRight:5}}
                    onClick={() => this.selectedStore(store)}>
                    <MenuItem title={storeItem}
                            className={this.props.className}
                    />
                </div>
            );
        }, this);

        return storesRender;
    }

    render(){
        try {
            const item = this.renderItem()
            return item
        } catch(err) {
            // console.log(err)
        }
        return ''
    }
}
export default Storeview