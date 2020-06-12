import React, {useState} from 'react'
import Identify from "src/simi/Helper/Identify";
import productBySkus from 'src/simi/queries/catalog/getProductsBySkusHome.graphql';
import { Simiquery } from 'src/simi/Network/Query';
import {productUrlSuffix, cateUrlSuffix} from 'src/simi/Helper/Url';
// import Griditem from './Homesection/Griditem';
// import {applySimiProductListItemExtraField} from 'src/simi/Helper/Product';
import Loading from 'src/simi/BaseComponents/Loading';
import ItemsCarousel from 'react-items-carousel';
import ChevronLeft from 'src/simi/App/Bianca/BaseComponents/Icon/ChevronLeft';
import ChevronRight from 'src/simi/App/Bianca/BaseComponents/Icon/ChevronRight';
import { smoothScrollToView } from 'src/simi/Helper/Behavior';
import { Link } from 'src/drivers';
require('./homesection.scss');

let loadedData = null

const HomeSection = props => {
    const { history, isPhone, data} = props;
    const {homesections} = data && data.home && data.home.homesections || [];
    const {storeConfig} = Identify.getStoreConfig() || {};
    const base_url = storeConfig && storeConfig.base_url || window.location.href || '';
    const [productsData, setProductsData] = useState(null);

    const [activeItemIndex, setActiveItemIndex] = useState(0);

    const scrollTop = () =>{
		smoothScrollToView($(".header-wrapper"), 300);
	}

    const onClickImage = (item, imageType) => {
        if (!(item instanceof Object)) return false;
        let valueKey = 'type_value_'+imageType;
        let url = '';
        let isNewWindow = false;
        if (parseInt(item.type) === 1) {
            //product detail
            if (item[valueKey]) {
                url = item[valueKey] + productUrlSuffix();
            }
        } else if(parseInt(item.type) === 2){
            //category
            if (item[valueKey]) {
                url = item[valueKey] + cateUrlSuffix();
            }
        } else {
            if (base_url && item[valueKey]) {
                const hostName = base_url.replace(/^http(s?):\/\//g, '').replace(/\/.*$/g, '');
                var regExpr = new RegExp(`^${hostName}`);
                if (item[valueKey].indexOf(hostName) !== -1) {
                    url = item[valueKey].replace(/^http(s?):\/\//g, '').replace(regExpr, '');
                }
            }
            if(item[valueKey] && item[valueKey].search(/^http(s?):\/\//) !== 0){
                url = item[valueKey];
            } else if (item[valueKey]) {
                url = item[valueKey];
                isNewWindow = true;
            }
        }
        if (url && !isNewWindow) {
            scrollTop();
        }
        setTimeout(() => {
            if (isNewWindow) {
                window.open(item[valueKey]);
            } else if(url) {
                history.push(url);
            }
        }, 300);
    }

    const onClickProduct = (location) => {
        history.push(location);
    }

    const carouselOnChange = (val, index) => {
        let newActiveValue = { ...activeItemIndex }
        newActiveValue[index] = val;
        setActiveItemIndex(newActiveValue);
    }

    const ItemRender = (props) => {
        const {item} = props
        let price = '';
        const currency = item.price.regularPrice.amount.currency || '$';
        const regular = item.price.regularPrice.amount.value || '$';
        if (item && item.special_price) {
            price = <div className="product-prices">
                <div className="regular">
                    <span className="price">{currency === '$' ? `${currency}${item.special_price}` : `${currency} ${item.special_price}`}</span>
                </div>
                <div className="special">
                    <span class="price old">{currency === '$' ? `${currency}${regular}` : `${currency} ${regular}`}</span>
                    {/* <span class="sale_off">-20%</span> */}
                </div>
            </div>
        } else {
            price = <div className="product-prices">
                <div className="regular">
                    <span className="price">{currency === '$' ? `${currency}${regular}` : `${currency} ${regular}`}</span>
                </div>
            </div>
        }
        return <div className="siminia-product-grid-item">
                <div className="grid-item-image" style={{position: "relative"}}>
                    <div className="siminia-product-image" style={{backgroundColor: "white"}}>
                        <div style={{position: "absolute", left: "0px", top: "0px", bottom: "0px", width: "100%"}}>
                            <Link to={`/${item.url_key}${productUrlSuffix()}`} title={item.sku}>
                                <img src={item.thumbnail.url} alt={item.thumbnail.label} />
                            </Link>
                        </div>
                    </div>
                </div>
                <div className="siminia-product-des">
                    <div className="product-des-info">
                        <div className="product-name">
                            <div className="product-name small">{item.name}</div>
                        </div>
                        <div className="vendor-and-price">
                            <div className="prices-layout " id="price-2204">
                                <div className="price-configurable">
                                    {price}
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
    }

    const renderProducts = (skus, index) => {
        if (skus && skus.length) {
            let productItems = productsData && productsData.items && productsData.items.filter((item) => skus.includes(item.sku)) || []
            if (isPhone && productsData && productItems.length) {
                    let carouselIndex = activeItemIndex[index] || 0;
                    if (Identify.isRtl()) {
                        if (carouselIndex === 0) carouselIndex = (productItems.length - 1);
                        productItems.items.reverse();
                    }
                    return <ItemsCarousel
                        infiniteLoop={false}
                        gutter={13} //Space between cards.
                        firstAndLastGutter={false}
                        activePosition={'center'}
                        disableSwipe={false}
                        alwaysShowChevrons={false}
                        numberOfCards={2}
                        slidesToScroll={1}
                        outsideChevron={true}
                        showSlither={false}
                        activeItemIndex={carouselIndex}
                        requestToChangeActive={(num) => carouselOnChange(num, index)}
                        chevronWidth={16}
                        leftChevron={<ChevronLeft className="chevron-left" style={{width: `16px`, height: `16px`}} />}
                        rightChevron={<ChevronRight className="chevron-right" style={{width: `16px`, height: `16px`}} />}
                        classes={{ wrapper: "wrapper", itemsWrapper: 'items-wrapper', itemsInnerWrapper: 'items-inner-wrapper', itemWrapper: 'item-wrapper', rightChevronWrapper: 'right-chevron-wrapper', leftChevronWrapper: 'left-chevron-wrapper' }}
                    >
                        {productItems.map((item, index) => {
                                return <ItemRender
                                    item={item}
                                    handleLink={onClickProduct}
                                    lazyImage={true}
                                    key={index} />
                            })
                        }
                    </ItemsCarousel>
                }

                return (productItems && productItems.map((item, index) => {
                        return <ItemRender
                            item={item}
                            handleLink={onClickProduct}
                            lazyImage={true}
                            key={index} />
                    })
                );
        }
        return '';
    }

    const renderSection = () => {
        let skus = {};
        homesections.map((item, index) => {
            if (!skus[index]) skus[index] = [];
            if (item.product_id_1 && parseInt(item.product_id_1) !== 0) skus[index].push(item.product_id_1);
            if (item.product_id_2 && parseInt(item.product_id_2) !== 0) skus[index].push(item.product_id_2);
            if (item.product_id_3 && parseInt(item.product_id_3) !== 0) skus[index].push(item.product_id_3);
            return true;
        })
        let allSkus = [];
        for (let i in skus) {
            skus[i].length && skus[i].forEach(sku => {
                allSkus.push(sku);
            });
        }

        const variables = {stringSkus: allSkus} /** All sku to query one time */

        return homesections.map((item, index) => {
            let sectionSkus = [];
            if (item.product_id_1 && parseInt(item.product_id_1) !== 0) sectionSkus.push(item.product_id_1);
            if (item.product_id_2 && parseInt(item.product_id_2) !== 0) sectionSkus.push(item.product_id_2);
            if (item.product_id_3 && parseInt(item.product_id_3) !== 0) sectionSkus.push(item.product_id_3);

            const imgLeft1 = isPhone && item.image_left_1_mobile ? item.image_left_1_mobile : item.image_left_1;
            const imgLeft1_alt = imgLeft1 && imgLeft1.split('/').pop().split('.')[0].replace(/[_-]/g, ' ');
            const imgLeft2 = isPhone && item.image_left_2_mobile ? item.image_left_2_mobile : item.image_left_2;
            const imgLeft2_alt = imgLeft2 && imgLeft2.split('/').pop().split('.')[0].replace(/[_-]/g, ' ');

            return (
                <div className="homesection" key={index}>
                    <div className={`left-sec ${sectionSkus.length === 0 ? 'no-products':''}`}>
                        {item.image_left_1 && 
                            <div className="image-1" onClick={() => onClickImage(item, '1')}>
                                <img src={imgLeft1} alt={imgLeft1_alt}/>
                            </div>
                        }
                        {item.image_left_2 && 
                            <div className="image-2" onClick={() => onClickImage(item, '2')}>
                                <img src={imgLeft2} alt={imgLeft2_alt}/>
                            </div>
                        }
                    </div>
                    {allSkus.length > 0 && sectionSkus.length > 0 &&
                        <div className="right-sec">
                            {index === 0 && productsData === null &&
                                <Simiquery query={productBySkus} variables={variables}>
                                    {({ loading, error, data }) => {
                                        if (error) return null;

                                        if (!data || !data.products) {
                                            return <Loading />
                                        }
                                        if (data.products.items.length === 0){
                                            return null;
                                        }
                                        let products = data && data.products || null;
                                        if (products) {
                                            setProductsData(products);
                                        }
                                        return null;
                                    }}
                                </Simiquery>
                            }
                            {index !== 0 && productsData === null &&
                                <Loading />
                            }
                            {productsData &&
                                renderProducts(sectionSkus, index)
                            }
                        </div>
                    }
                </div>
            );
        });
    }

    return (
        <div className={`section-homepage ${isPhone ? 'mobile':''}`}>
            <div className={`container home-container`}>
                {renderSection()}
            </div>
        </div>
    ) ;
}

export default HomeSection;