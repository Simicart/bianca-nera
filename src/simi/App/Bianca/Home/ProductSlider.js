import React, { useEffect, useState } from 'react'
import Identify from "src/simi/Helper/Identify";
import { simiUseQuery } from 'src/simi/Network/Query' 
import getCategory from 'src/simi/queries/catalog/getCategory.graphql'
import Loading from "src/simi/BaseComponents/Loading";
import { GridItem } from "src/simi/App/Bianca/BaseComponents/GridItem";
import {applySimiProductListItemExtraField} from 'src/simi/Helper/Product';
import ItemsCarousel from 'react-items-carousel';
import ChevronLeft from 'src/simi/App/Bianca/BaseComponents/Icon/ChevronLeft';
import ChevronRight from 'src/simi/App/Bianca/BaseComponents/Icon/ChevronRight';
import useWindowSize from 'src/simi/App/Bianca/Hooks';

const responsive = {
    superLargeDesktop: {
      breakpoint: { max: 4000, min: 1921 },
      items: 5,
      chevronWidth: 72,
      iconWidth: 24
    },
    desktop: {
      breakpoint: { max: 1921, min: 1176 },
      items: 4,
      chevronWidth: 72,
      iconWidth: 24
    },
    desktopSmall: {
      breakpoint: { max: 1176, min: 1024 },
      items: 2,
      chevronWidth: 72,
      iconWidth: 24
    },
    tablet: {
      breakpoint: { max: 1024, min: 587 },
      items: 2,
      chevronWidth: 20,
      iconWidth: 16
    },
    mobile: {
      breakpoint: { max: 587, min: 0 },
      items: 2,
      chevronWidth: 20,
      iconWidth: 16
    },
};

const ProductSlider = props => {
    const { dataProduct, history} = props;
    const [queryResult, queryApi] = simiUseQuery(getCategory);
    const [activeItemIndex, setActiveItemIndex] = useState(0);
    const {width} = useWindowSize();
    const {data} = queryResult
    const {runQuery} = queryApi
  
    useEffect(() => {
        runQuery({
            variables: {
                id: Number(dataProduct.category_id),
                pageSize: Number(20),
                currentPage: Number(1),
                stringId: String(dataProduct.category_id)
            }
        })
    },[])

    const handleAction = (location) => {
        history.push(location);
    }

    if(!data) return <Loading />

    const renderProductItem = (item,lastInRow) => {
        const itemData =  {
            ...item,
            small_image:
                typeof item.small_image === 'object' && item.small_image.hasOwnProperty('url') ? item.small_image.url : item.small_image
        }
        return (
            <div
                key={`horizontal-item-${item.id}`}
                className={`horizontal-item ${lastInRow? 'last':'middle'}`}
                >
                <GridItem
                    item={itemData}
                    handleLink={handleAction}
                    lazyImage={true}
                />
            </div>
        );
    }

    const sortItemDesc = (items) => {
        return items.sort((a, b) => b.id - a.id )
    }

    // calculate items number for Carousel
    const _responseSize = Object.values(responsive);
    const breakPoint = _responseSize.find((itemSize) => {
        if (itemSize.breakpoint) {
            if (width >= itemSize.breakpoint.min && width < itemSize.breakpoint.max) {
                return true;
            }
        }
        return false;
    });
    let numberCards = 4, chevWidth = 72, iconWidth = 24; // default values
    if (breakPoint.items) {
        numberCards = breakPoint.items;
        chevWidth = breakPoint.chevronWidth;
        iconWidth = breakPoint.iconWidth;
    }

    const renderProductGrid = (items) => {
        const itemsAfterSort = sortItemDesc(items);
        const products = itemsAfterSort.map((item, index) => {
            return renderProductItem(item, (index % 4 === 3))
        });
        
        return (
            <ItemsCarousel
                infiniteLoop={false}
                gutter={16} //Space between cards.
                firstAndLastGutter={false}
                activePosition={'center'}
                disableSwipe={false}
                alwaysShowChevrons={false}
                numberOfCards={numberCards}
                slidesToScroll={1}
                outsideChevron={true}
                showSlither={false}
                activeItemIndex={activeItemIndex}
                requestToChangeActive={setActiveItemIndex}
                chevronWidth={chevWidth}
                leftChevron={<ChevronLeft className="chevron-left" style={{width: `${iconWidth}px`, height: `${iconWidth}px`}} />}
                rightChevron={<ChevronRight className="chevron-right" style={{width: `${iconWidth}px`, height: `${iconWidth}px`}} />}
                classes={{ wrapper: "wrapper", itemsWrapper: 'items-wrapper', itemsInnerWrapper: 'items-inner-wrapper', itemWrapper: 'item-wrapper', rightChevronWrapper: 'right-chevron-wrapper', leftChevronWrapper: 'left-chevron-wrapper' }}
            >
                {products}
            </ItemsCarousel>
        )
    }

    if(data.simiproducts.hasOwnProperty('items') && data.simiproducts.total_count > 0) {
        const productItem = applySimiProductListItemExtraField(data.simiproducts);
        return (
            <div className="product-list">
                <div className="product-horizotal">
                    {renderProductGrid(productItem.items)}
                </div>
            </div>
        )
 
    }

    return 'Product was found';
}

export default ProductSlider;