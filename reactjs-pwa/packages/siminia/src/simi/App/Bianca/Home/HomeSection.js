import React, {useState} from 'react'
import Identify from "src/simi/Helper/Identify";
import productBySkus from 'src/simi/queries/catalog/getProductsBySkus.graphql';
import { Simiquery } from 'src/simi/Network/Query';
import {productUrlSuffix, cateUrlSuffix} from 'src/simi/Helper/Url';
import Griditem from './Homesection/Griditem';
import {applySimiProductListItemExtraField} from 'src/simi/Helper/Product';
import Loading from 'src/simi/BaseComponents/Loading';
import ItemsCarousel from 'react-items-carousel';
import ChevronLeft from 'src/simi/App/Bianca/BaseComponents/Icon/ChevronLeft';
import ChevronRight from 'src/simi/App/Bianca/BaseComponents/Icon/ChevronRight';
import { smoothScrollToView } from 'src/simi/Helper/Behavior';
require('./homesection.scss');

let loadedData = null

const HomeSection = props => {
    const { history, isPhone, data} = props;
    const {homesections} = data && data.home && data.home.homesections || [];
    const {storeConfig} = Identify.getStoreConfig() || {};
    const base_url = storeConfig && storeConfig.base_url || window.location.href || '';

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

    const renderProducts = (skus, index) => {
        if (skus.length) {
            let variables = {
                stringSku: skus,
                pageSize: 3,
                currentPage: 1
            }
            return <Simiquery query={productBySkus} variables={variables}>
                {({ loading, error, data }) => {
                    if (error) return null;
                    //if (error) return <div>{Identify.__('Data Fetch Error')}</div>;
                    //prepare data
                    if (data && data.simiproducts) {
                        data.products = applySimiProductListItemExtraField(data.simiproducts)
                        if (data.products.simi_filters)
                            data.products.filters = data.products.simi_filters
                            
                        const stringVar = JSON.stringify({...variables, ...{currentPage: 0}})
                        if (!loadedData || !loadedData.vars || loadedData.vars !== stringVar) {
                            loadedData = data
                        } else {
                            let loadedItems = loadedData.products.items
                            const newItems = data.products.items
                            loadedItems = loadedItems.concat(newItems)
                            for(var i=0; i<loadedItems.length; ++i) {
                                for(var j=i+1; j<loadedItems.length; ++j) {
                                    if(loadedItems[i] && loadedItems[j] && loadedItems[i].id === loadedItems[j].id)
                                        loadedItems.splice(j--, 1);
                                }
                            }
                            loadedData.products.items = loadedItems
                        }
                        loadedData.vars = stringVar
                    }
                    if (loadedData && loadedData.category && parseInt(loadedData.category.id) === parseInt(id)){
                        data = loadedData
                    }
                
                    if (!data || !data.simiproducts) {
                        return <Loading />
                    }
                    if (data.products.items.length === 0){
                        return null;
                    }

                    //let products = data && data.products || null;
                    let products = data && data.simiproducts || null;

                    if (isPhone && products && products.items.length
                    ) {
                        let carouselIndex = activeItemIndex[index] || 0;
                        if (Identify.isRtl()) {
                            if (carouselIndex === 0) carouselIndex = (products.items.length - 1);
                            products.items.reverse();
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
                            {products.items.map((item, index) => {
                                    return <Griditem
                                        item={item}
                                        handleLink={onClickProduct}
                                        lazyImage={true}
                                        key={index} />
                                })
                            }
                        </ItemsCarousel>
                    }

                    return (products && products.items.map((item, index) => {
                            return <Griditem
                                item={item}
                                handleLink={onClickProduct}
                                lazyImage={true}
                                key={index} />
                        })
                    );
                }}
            </Simiquery>
        }
        return '';
    }

    const renderSection = () => {
        return homesections.map((item, index) => {
            let skus = [];
            if (item.product_id_1 && parseInt(item.product_id_1) !== 0) skus.push(item.product_id_1);
            if (item.product_id_2 && parseInt(item.product_id_2) !== 0) skus.push(item.product_id_2);
            if (item.product_id_3 && parseInt(item.product_id_3) !== 0) skus.push(item.product_id_3);

            const imgLeft1 = isPhone && item.image_left_1_mobile ? item.image_left_1_mobile : item.image_left_1;
            const imgLeft1_alt = imgLeft1 && imgLeft1.split('/').pop().split('.')[0].replace(/[_-]/g, ' ');
            const imgLeft2 = isPhone && item.image_left_2_mobile ? item.image_left_2_mobile : item.image_left_2;
            const imgLeft2_alt = imgLeft2 && imgLeft2.split('/').pop().split('.')[0].replace(/[_-]/g, ' ');

            return (
                <div className="homesection" key={index}>
                    <div className={`left-sec ${skus.length === 0 ? 'no-products':''}`}>
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
                    {skus.length > 0 &&
                        <div className="right-sec">
                            {renderProducts(skus, index)}
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