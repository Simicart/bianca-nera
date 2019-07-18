import React, { useEffect } from 'react'
import Identify from "src/simi/Helper/Identify";
import { simiUseQuery } from 'src/simi/Network/Query' 
import getCategory from 'src/simi/queries/catalog/getCateProductsNoFilter.graphql'
import Loading from "src/simi/BaseComponents/Loading";
import { GridItem } from "src/simi/BaseComponents/GridItem";

const ProductItem = props => {
    const {classes, dataProduct, history} = props;
    const [queryResult, queryApi] = simiUseQuery(getCategory, false);
    const {data} = queryResult
    const {runQuery} = queryApi
  

    useEffect(() => {
        runQuery({
            variables: {
                // id: Number(id),
                pageSize: Number(8),
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
                className={`${classes["horizontal-item"]} ${lastInRow? 'last':classes['middle']}`}
                style={{
                    display: 'inline-block', 
                }}
                >
                <GridItem
                    item={itemData}
                    handleLink={handleAction}
                />
            </div>
        );
    }

    const renderProductGrid = (items) => {
        const products = items.map((item, index) => {
            return renderProductItem(item, (index % 4 === 3))
        });
        
        return (
            <div className={classes["horizontal-flex"]} style={{
                width: '100%',
                flexWrap: 'wrap',
                display: 'flex',
                direction: Identify.isRtl()?'rtl':'ltr'
            }}>
                {products}
            </div>
        )
    }

    if(data.products.hasOwnProperty('items') && data.products.total_count > 0) {
        return (
            <div className={classes["product-list"]}>
                <div className={classes["product-horizotal"]}>
                    {renderProductGrid(data.products.items)}
                </div>
            </div>
        )
 
    }

    return 'Product was found';
}

export default ProductItem;