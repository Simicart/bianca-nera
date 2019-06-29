import React, { useEffect } from 'react';
import { bool, func, shape, string } from 'prop-types';
import { simiUseQuery } from 'src/simi/Network/Query'

import { mergeClasses } from 'src/classify';
import PRODUCT_SEARCH from 'src/simi/queries/productSearch.graphql';
import SIMI_PRODUCT_SEARCH from 'src/simi/queries/simiconnector/productSearch.graphql';
import Suggestions from './suggestions';
import Close from 'src/simi/BaseComponents/Icon/TapitaIcons/Close'
import defaultClasses from './searchAutoComplete.css';
import Identify from 'src/simi/Helper/Identify'

const SearchAutoComplete = props => {
    const { setVisible, visible, value } = props;
    const searchQuery = Identify.hasConnector()?SIMI_PRODUCT_SEARCH:PRODUCT_SEARCH
    const [queryResult, queryApi] = simiUseQuery(searchQuery);
    const { data, error, loading } = queryResult;
    const { resetState, runQuery, setLoading } = queryApi;

    const valid = value && value.length > 2;

    const classes = mergeClasses(defaultClasses, props.classes);
    const rootClassName = visible ? classes.root_visible : classes.root_hidden;
    let message = '';

    if(data && data.simiproducts)
        data.products = data.simiproducts

    if (error) {
        message = 'An error occurred while fetching results.';
    } else if (loading) {
        message = 'Fetching results...';
    } else if (!data) {
        message = 'Search for a product';
    } else if (!data.products.items.length) {
        message = 'No results were found.';
    } else {
        message = `${data.products.items.length} items`;
    }

    // run the query once on mount, and again whenever state changes
    useEffect(() => {
        if (visible && valid) {
            setLoading(true);
            runQuery({ variables: { inputText: value } });
        } else if (!value) {
            resetState();
        }
    }, [resetState, runQuery, setLoading, valid, value, visible]);

    return (
        <div className={rootClassName}>
            <div role="button" tabIndex="0" className={classes['close-icon']} onClick={() => setVisible(false)} onKeyUp={() => setVisible(false)}>
                <Close style={{width: 14, height: 14, display: 'block'}} />
            </div>
            <div className={classes.message}>{message}</div>
            <div className={classes.suggestions}>
                <Suggestions
                    products={data ? data.products : {}}
                    searchValue={value}
                    setVisible={setVisible}
                    visible={visible}
                />
            </div>
        </div>
    );
};

export default SearchAutoComplete;

SearchAutoComplete.propTypes = {
    classes: shape({
        message: string,
        root_hidden: string,
        root_visible: string,
        suggestions: string
    }),
    setVisible: func,
    visible: bool
};