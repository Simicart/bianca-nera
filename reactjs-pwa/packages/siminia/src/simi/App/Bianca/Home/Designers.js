import React from 'react'
import Identify from "src/simi/Helper/Identify";
import {cateUrlSuffix} from 'src/simi/Helper/Url';
import Scroller from "./Scroller";
import {smoothScrollToView} from 'src/simi/Helper/Behavior';

const $ = window.$;

const Designers = props => {
    const { history, isPhone} = props;
    const storeConfig = Identify.getStoreConfig() || {};
    const {simiStoreConfig} = storeConfig || {};
    const {config} = simiStoreConfig || {};
    const {vendor_list: data} = config || {};

    const slideSettings = {
        chevronWidth: isPhone ? 16 : 54,
        showChevron: true,
        numberOfCards: isPhone ? 3 : 6,
        slidesToScroll: 3,
        gutter: isPhone ? 12.5 : 16
    }

    let newData = [];
    if (data) {
        data.forEach((item, index)=>{
            if (index < 18 && item.logo) {
                item.url = `/designers/${item.vendor_id}.html`;
                item.image = item.logo;
                item.alt = item.logo && item.logo.split('/').pop().split('.')[0].replace(/[_-]/g, ' ');
                newData.push(item);
            }
            return false;
        });
    }

    const onClickItem = () => {
        smoothScrollToView($('#siminia-main-page'), 350);
    }

    const actionViewAll = () => {
        smoothScrollToView($('#siminia-main-page'), 350);
        setTimeout(()=>{
            history.push('/designers' + cateUrlSuffix());
        }, 350);
    }

    const lastItems = [(
        <div className="last-items" key={Identify.randomString(3)}>
            <div className="btn" onClick={actionViewAll}><span>{Identify.__('View all')}</span></div>
        </div>
    )];

    let startItemIndex = 0;
    if (Identify.isRtl()) {
        newData.reverse();
        startItemIndex = (newData.length - 1)
    }

    return (
        <div className={`brand-slider ${isPhone ? 'phone-view':''}`}>
            { data && 
                <div className="title-box">
                    <h3 className="title">{Identify.__('Shop By Designers')}</h3>
                </div>
            }
            <Scroller data={newData} 
                initItemIndex={startItemIndex} 
                lastItems={lastItems} 
                slideSettings={slideSettings} 
                isPhone={isPhone} history={history} 
                onClickItem={onClickItem}
            />
        </div>
    );
}

export default Designers;