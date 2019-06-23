import React from 'react'
import Identify from "src/simi/Helper/Identify"
import {Route, Switch} from "react-router";
import {PbPageHoc} from "src/simi/App/core/PbPage";
import ErrorView from 'src/components/ErrorView/index';
import { Page } from '@magento/peregrine';

const renderRoutingError = props => <ErrorView {...props} />;

class Abstract extends React.Component{
    render(){
        const simicartConfig = Identify.getAppDashboardConfigs() !== null ? Identify.getAppDashboardConfigs()
            : Identify.getAppDashboardConfigsFromLocalFile();
        const merchantConfig = Identify.getStoreConfig();
        if(simicartConfig
            && merchantConfig
            && merchantConfig.simiStoreConfig)
        {
            //add rtl
            this.renderRTL(merchantConfig.simiStoreConfig)
        }
        return (
            this.renderLayout()
        )
    }

    renderLayout = ()=>{
        return null;
    }

    /**
    * Page builder routes
    * @returns array
    */
    renderPbRoute = () => {
        const pbRoutes = []
        const simicartConfig = Identify.getAppDashboardConfigs() !== null ? Identify.getAppDashboardConfigs()
        : Identify.getAppDashboardConfigsFromLocalFile();
        if (simicartConfig) {
            const config = simicartConfig['app-configs'][0];
            if (
                config.api_version &&
                parseInt(config.api_version, 10) &&
                config.themeitems &&
                config.themeitems.pb_pages &&
                config.themeitems.pb_pages.length
                ) {
                const merchantConfigs = Identify.getStoreConfig();
                if (merchantConfigs &&
                    merchantConfigs.storeConfig &&
                    merchantConfigs.storeConfig.id) {
                    const storeId = merchantConfigs.storeConfig.id
                    config.themeitems.pb_pages.forEach(element => {
                        if (
                            element.url_path &&
                            element.url_path !== '/' &&
                            element.storeview_visibility &&
                            (element.storeview_visibility.split(',').indexOf(storeId.toString()) !== -1)
                        ){
                            const routeToAdd = {
                                path : element.url_path,
                                render: (props) => <PbPageHoc {...props} pb_page_id={element.entity_id}/>
                            }
                            pbRoutes.push(<Route key={`pb_page_${element.entity_id}`} exact {...routeToAdd}/>)
                        }
                    });
                }
            }
        }
        return pbRoutes
    }

    renderRoute =(router = null)=>{
        if(!router) return <div></div>
        return (
            <Switch>
                <Route exact {...router.search_page}/>
                <Route exact {...router.register}/>
                <Route exact {...router.cart}/>
                <Route exact {...router.product_detail}/>
                <Route exact {...router.checkout}/>
                <Route exact {...router.account}/>
                <Route exact {...router.login}/>
                <Route exact {...router.logout}/>
                <Route exact {...router.wishlist}/>
                {this.renderPbRoute()}
                <Route render={() => <Page>{renderRoutingError}</Page>} />
            </Switch>
        )
    }


    renderRTL = (simiStoreConfig)=>{
        //add rtl
        if (simiStoreConfig.store && parseInt(simiStoreConfig.store.base.is_rtl, 10) === 1) {
            console.log('Is RTL');
        } else {
            try {
                document.getElementById("rtl-stylesheet").remove();
            }
            catch (err) {
                
            }
        }
    }
}

export default Abstract