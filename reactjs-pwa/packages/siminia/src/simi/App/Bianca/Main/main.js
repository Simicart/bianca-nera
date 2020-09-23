import React, { Component } from 'react';
import { bool } from 'prop-types';

import Header from 'src/simi/App/Bianca/Header'
import Footer from 'src/simi/App/Bianca/Footer';
import Identify from 'src/simi/Helper/Identify'
import {saveCategoriesToDict} from 'src/simi/Helper/Url'
// import Connection from 'src/simi/Network/SimiConnection'
import LoadingComponent  from 'src/simi/BaseComponents/Loading'
import * as Constants from 'src/simi/Config/Constants';
import simiStoreConfigDataQuery from 'src/simi/queries/getStoreConfigData.graphql'
import { Simiquery } from 'src/simi/Network/Query'
import classes from './main.css';
import { withRouter } from 'react-router-dom';
import Homeskeleton from './Skeleton/Home';
import Skeleton from 'react-loading-skeleton';

import { Util } from '@magento/peregrine';
const { BrowserPersistence } = Util;
const storage = new BrowserPersistence();

class Main extends Component {

    componentDidMount() {
        const dbConfig = Identify.getAppDashboardConfigs()
        if (!dbConfig) {
            // Connection.connectSimiCartServer('GET', true, this);
        }
    }

    static propTypes = {
        isMasked: bool
    };

    get classes() {
        const { classes } = this.props;

        return ['page', 'root'].reduce(
            (acc, val) => ({ ...acc, [val]: classes[`${val}`] }),
            {}
        );
    }

    mainContent(storeConfig = null) {
        const { history } = this.props;
        const mageStore = storeConfig && storeConfig.storeConfig || null;
        if (mageStore && mageStore.locale === 'ar_KW') {
            import('src/fonts/Droidkufi.css');
            document.documentElement.classList.add('font_ar_KW');
        }
        return (
            <React.Fragment>
                <Header storeConfig={storeConfig}/>
                <div id="data-breadcrumb"/>
                {storeConfig ? <div className={`${classes.page}`} id="siminia-main-page">{this.props.children}</div> : 
                    history && history.location && location.pathname === '/' ? <Homeskeleton />:
                    <Skeleton height={438}/>
                }
                <Footer storeConfig={storeConfig}/>
            </React.Fragment>
        )
    }
    render() {
        const {store_id, currency} = Identify.getAppSettings() || {};
        const cartId = storage.getItem('cartId')
        return (
            <main className={classes.root}>
                <div className="app-loading" style={{display:'none'}} id="app-loading">
                    <LoadingComponent/>
                </div>
                { Identify.getDataFromStoreage(Identify.SESSION_STOREAGE, Constants.STORE_CONFIG) ?
                    this.mainContent(Identify.getDataFromStoreage(Identify.SESSION_STOREAGE, Constants.STORE_CONFIG)) :
                    <Simiquery query={simiStoreConfigDataQuery} variables={{storeId: store_id, currency, cartId: cartId?cartId:0}}>
                        {({ data }) => {
                            if (data && data.storeConfig) {
                                Identify.saveStoreConfig(data)
                                saveCategoriesToDict(data.simiRootCate)
                                return this.mainContent(data)
                            }
                            return this.mainContent()
                        }}
                    </Simiquery>
                }
            </main>
        );
    }
}

export default withRouter(Main);
