import React, { useEffect, useState } from 'react'
import { getHomeData } from 'src/simi/Model/Home';
import Banner from './Banner';
import HomeCat from "./HomeCat";
// import Brands from "./Brands";
import HomeSection from "./HomeSection";
import LoadingSpiner from 'src/simi/BaseComponents/Loading/LoadingSpiner'
import { withRouter } from 'react-router-dom';
// import ProductList from './ProductList';
import Identify from 'src/simi/Helper/Identify';
// import * as Constants from 'src/simi/Config/Constants';
import { getOS } from 'src/simi/App/Bianca/Helper';
import Designers from './Designers';
// import Newcollections from './Newcollections';
import Instagram from './Instagram';
import TitleHelper from 'src/simi/Helper/TitleHelper';
// import Chats from 'src/simi/App/Bianca/BaseComponents/Chats';
import LazyLoad from 'react-lazyload';
import Skeleton from 'react-loading-skeleton';
require('./home.scss');

if (getOS() === 'MacOS') require('./home-ios.scss');

const Home = props => {
    const { history } = props;
    const [isPhone, setIsPhone] = useState(window.innerWidth < 1024)
    // const simiSessId = Identify.getDataFromStoreage(Identify.LOCAL_STOREAGE, Constants.SIMI_SESS_ID)
    const cached_home = Identify.ApiDataStorage(`home_lite`)
    const storeConfig = Identify.getStoreConfig() || {};
    const config = storeConfig.simiStoreConfig && storeConfig.simiStoreConfig.config || {};
    const { brands, instagram, seo } = config || [];
    const [data, setHomeData] = useState(cached_home);

    const resizePhone = () => {
        window.onresize = function () {
            const width = window.innerWidth;
            const newIsPhone = width < 1024
            if (isPhone !== newIsPhone) {
                setIsPhone(newIsPhone)
            }
        }
    }
    useEffect(() => {
        if (!data) {
            getHomeData(setData);
        }
        resizePhone();
    }, [data, isPhone])

    const setData = (data) => {
        if (!data.errors) {
            Identify.ApiDataStorage(`home_lite`, 'update', data);
            setHomeData(data);
        }
    }

    // if(!data) {
    //     return <LoadingSpiner />
    // } 

    const home_meta = seo && seo.home_meta && seo.home_meta || null;

    return (
        <div className={`home-wrapper ${getOS()}`}>
            {TitleHelper.renderMetaHeader({
                title: Identify.__(home_meta && home_meta.title || 'Bianca Nera'),
                desc: Identify.__(home_meta && home_meta.desc || '')
            })}
            <div className={`banner-wrap ${isPhone ? 'mobile' : ''}`}>
                <Banner data={data} history={history} isPhone={isPhone} />
            </div>

            <HomeSection data={data} history={history} isPhone={isPhone} />

            {/* {brands && 
                <div className={`shop-by-brand-wrap ${isPhone ? 'mobile':''}`}>
                    <Brands data={brands} history={history} isPhone={isPhone}/>
                </div>
            } */}
            {/* <div className={`featured-products-wrap ${isPhone ? 'mobile':''}`}>
                <ProductList homeData={data} history={history}/>
            </div> */}
            {/* <div className={`popular-categories-wrap ${isPhone ? 'mobile':''}`}>
                <HomeCat catData={data} history={history} isPhone={isPhone}/>
            </div> */}
            {/* <div className={`new-collections-wrap ${isPhone ? 'mobile':''}`}>
                <Newcollections data={data} history={history} isPhone={isPhone}/>
            </div> */}
            <LazyLoad placeholder={<div className="container"><Skeleton height={isPhone ? 153 :214} style={{width: '100%'}}/></div>} 
                height={isPhone ? 153 :214} offset={50} once={true}>
                <Designers history={history} isPhone={isPhone} />
            </LazyLoad>
            {
                (instagram && instagram.enabled === '1' && instagram.userid) ?
                    <LazyLoad placeholder={<LoadingSpiner />}>
                        <div className={`shop-our-instagram-wrap ${isPhone ? 'mobile' : ''}`}>
                            <Instagram data={instagram.userid} history={history} isPhone={isPhone} />
                        </div>
                    </LazyLoad> : ''
            }
            {/* <div className={`home-chats ${isPhone ? 'mobile':''}`}>
                <Chats data={instant_contact} history={history} isPhone={isPhone}/>
            </div> */}
        </div>
    );
}

export default withRouter(Home);