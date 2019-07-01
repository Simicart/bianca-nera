import React, { useEffect } from 'react';
import LoadingSpiner from 'src/simi/BaseComponents/Loading/LoadingSpiner'
import { number } from 'prop-types';
import { simiUseQuery } from 'src/simi/Network/Query';
import categoryQuery from 'src/simi/queries/getCategory.graphql';
import simicntrCategoryQuery from 'src/simi/queries/simiconnector/getCategory.graphql'
import Products from 'src/simi/BaseComponents/Products';
import { resourceUrl } from 'src/simi/Helper/Url'
import CategoryHeader from './categoryHeader'
import Identify from 'src/simi/Helper/Identify';
import ObjectHelper from 'src/simi/Helper/ObjectHelper';
import { withRouter } from 'react-router-dom';


var sortByData = null
var filterData = null

const Category = props => {
    const { id } = props;
    let pageSize = Identify.findGetParameter('product_list_limit')
    pageSize = pageSize?Number(pageSize):window.innerWidth < 1024?12:24
    let currentPage = Identify.findGetParameter('page')
    currentPage = currentPage?Number(currentPage):1
    
    const productListOrder = Identify.findGetParameter('product_list_order')
    const productListDir = Identify.findGetParameter('product_list_dir')
    
    const newSortByData = productListOrder?productListDir?{[productListOrder]: productListDir.toUpperCase()}:{[productListOrder]: 'ASC'}:null
    if (newSortByData && (!sortByData || !ObjectHelper.shallowEqual(sortByData, newSortByData))) {
        sortByData = newSortByData
    }

    const productListFilter = Identify.findGetParameter('filter')
    if (productListFilter) {
        if (JSON.parse(productListFilter)){
            filterData = productListFilter
        }
    }

    const [queryResult, queryApi] = simiUseQuery(Identify.hasConnector()?simicntrCategoryQuery:categoryQuery);
    const { data, error, loading } = queryResult;
    const { runQuery, setLoading } = queryApi;

    useEffect(() => {
        const variables = {
            id: Number(id),
            pageSize: pageSize,
            currentPage: currentPage,
            stringId: String(id),
            simiFilter: filterData
        }
        if (sortByData)
            variables.sort = sortByData
        
        setLoading(true);
        runQuery({
            variables: variables
        });
        window.scrollTo({
            left: 0,
            top: 0,
            behavior: 'smooth'
        });
    }, [id, pageSize, currentPage, sortByData, filterData]);
    if (data && data.simiproducts) {
        data.products = data.simiproducts
        if (data.products.simi_filters)
            data.products.filters = data.products.simi_filters
    }

    if (error) return <div>Data Fetch Error</div>;
    // show loading indicator until our data has been fetched and pagination state has been updated
    //if (!totalPages) return <LoadingSpiner />;
    if (!data || !data.category) return <LoadingSpiner />;

    const categoryTitle = data && data.category ? data.category.name : '';
    // if our data is still loading, we want to reset our data state to null
    return (
        <div className="container">
            {
            (data.category && data.category.name && data.category.image) &&
            <CategoryHeader
                name={data.category.name}
                image_url={resourceUrl(data.category.image, { type: 'image-category' })}
            />
            }
            <Products
                title={categoryTitle}
                history={props.history}
                location={props.location}
                currentPage={currentPage}
                pageSize={pageSize}
                data={loading ? null : data}
                sortByData={sortByData}
                filterData={filterData?JSON.parse(productListFilter):null}
            />
        </div>
    );
};

Category.propTypes = {
    id: number,
    pageSize: number
};

Category.defaultProps = {
    id: 3,
    pageSize: 12
};

export default (withRouter)(Category);