import React from 'react'
import {Carousel} from "react-responsive-carousel";
import 'react-responsive-carousel/lib/styles/carousel.min.css';
import Identify from "src/simi/Helper/Identify";
import BannerItem from "./BannerItem";
import Skeleton from 'react-loading-skeleton';
require('./banner.scss');

const Banner = props => {
    const { history, isPhone, data} = props;
    const {home} = data || {};
    const {homebanners} = home && home.homebanners || [];
    // const bannerCount = data.length;

    const Prev = (clickHandler, hasPrev, label) => {
        return '<';
    }

    const Next = (clickHandler, hasPrev, label) => {
        return '>';
    }

    const slideSettings = {
        autoPlay: false,
        showArrows: true,
        showThumbs: false,
        // showIndicators: (bannerCount && bannerCount === 1) ? false : true,
        showIndicators: false,
        showStatus: false,
        infiniteLoop: true,
        rtl: Identify.isRtl() === true,
        lazyLoad: true,
        dynamicHeight : true,
        transitionTime : 500,
        selectedItem: 0
    }

    const bannerData = [];
    if (homebanners instanceof Array) {
        homebanners.forEach((item, index) => {
            if (item.banner_name || item.banner_name_tablet) {
                bannerData.push(
                    <div
                        key={index}
                        style={{cursor: 'pointer'}}
                    >
                        <BannerItem item={item}  history={history} isPhone={isPhone}/>
                    </div>
                )
            }
        });
    }

    if (Identify.isRtl()) {
        bannerData.reverse();
        slideSettings.selectedItem = (bannerData.length - 1)
    }

    return (
        <div className={`banner-homepage ${Identify.isRtl() ? 'banner-home-rtl' : ''}`} style={{direction: 'ltr'}}>
            <div className={`container home-container`}>
                {bannerData.length > 0 ?
                    <Carousel {...slideSettings}>
                        {bannerData}
                    </Carousel>:
                    <Skeleton height={438}/>
                }
            </div>
        </div>
    ) ;
}

export default Banner;