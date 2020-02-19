import React from 'react'
import {Colorbtn} from 'src/simi/BaseComponents/Button'
import {productUrlSuffix, cateUrlSuffix} from 'src/simi/Helper/Url';
import Identify from 'src/simi/Helper/Identify';

const BannerItem = props => {
    const { history, item, isPhone } = props;
    const {storeConfig} = Identify.getStoreConfig() || {};
    const base_url = storeConfig && storeConfig.base_url || window.location.href || '';

    const handleOnClickBanner = (e) => {
        if (parseInt(item.type, 10) === 1) {
            //product detail
            if (item.url_key) {
                history.push(item.url_key + productUrlSuffix());
            }
        } else if(parseInt(item.type, 10) === 2){
            //category
            if (item.url_path) {
                history.push(item.url_path + cateUrlSuffix());
            }
        } else if(item.banner_url) {
            e.preventDefault();
            if (base_url) {
                const hostName = base_url.replace(/^http(s?):\/\//g, '').replace(/\/.*$/g, '');
                var regExpr = new RegExp(`^${hostName}`);
                if (item.banner_url.indexOf(hostName) !== -1) {
                    history.push(
                        item.banner_url.replace(/^http(s?):\/\//g, '').replace(regExpr, '')
                    );
                    return;
                }
            }
            if(item.banner_url.search(/^http(s?):\/\//) !== 0){
                history.push(item.banner_url);
                return;
            }
            window.open(item.banner_url);
        }
    }

    const renderBannerTitle = item => {
        return(
            <div role="presentation" className="banner-title">
                {item.show_title && parseInt(item.show_title, 10) ? <div className="bannner-content">
                    <div className="title">{item.banner_title}</div>
                </div> : ''}
                {item.button_label && <Colorbtn
                    text={item.button_label}
                    className="banner-action"/>}
            </div>
        )
    }

    const w = '100%';
    const h = '100%';
    let img = '';
    if(isPhone) {
        if(item.banner_name_tablet) {
            img = item.banner_name_tablet;
        } else if(item.banner_name) {
            img = item.banner_name
        }
    } else {
        if(item.banner_name) {
            img = item.banner_name;
        } else if(item.banner_name_tablet) {
            img = item.banner_name_tablet
        }
    }
    if(!img) return null

    return (
        <div
            style={{position: 'relative', maxWidth: w, minHeight: h}}
            className="banner-item"
            onClick={(e) => handleOnClickBanner(e)}
        >
            {renderBannerTitle(item)}
            <img className="img-responsive" width={w} height={h} src={img} alt={item.banner_title}/>
        </div>
    )
}

export default BannerItem;