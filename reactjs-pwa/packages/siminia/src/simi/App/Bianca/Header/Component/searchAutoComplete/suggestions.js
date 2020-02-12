import React, { Fragment, useCallback } from 'react';
import { arrayOf, bool, func, shape, string } from 'prop-types';

import { mergeClasses } from 'src/classify';
import SuggestedCategories from './suggestedCategories';
import SuggestedProducts from './suggestedProducts';
import defaultClasses from './suggestions.css';
import Identify from 'src/simi/Helper/Identify'

import { analyticImpressionsGTM } from 'src/simi/Helper/Analytics';

const Suggestions = props => {
    const { products, searchValue, setVisible, visible } = props;
    const { filters, items } = products;
    const classes = mergeClasses(defaultClasses, props.classes);

    const onNavigate = useCallback(() => {
        setVisible(false);
    }, [setVisible]);

    if (!visible || !filters || !items) {
        return null;
    }

    const categoryFilter =
        filters.find(({ name }) => name === 'Category') || {};
    const categories = categoryFilter.filter_items || [];

    analyticImpressionsGTM(items, '', 'Search Results Suggestion');

    return (
        <Fragment>
            <SuggestedCategories
                categories={categories}
                onNavigate={onNavigate}
                value={searchValue}
                classes={classes}
            />
            <h2 className={classes.heading}>
                <span>{Identify.__('Product Suggestions')}</span>
            </h2>
            <SuggestedProducts onNavigate={onNavigate} products={items} />
        </Fragment>
    );
};

export default Suggestions;

Suggestions.propTypes = {
    classes: shape({
        heading: string
    }),
    products: shape({
        filters: arrayOf(
            shape({
                filter_items: arrayOf(shape({})),
                name: string.isRequired
            }).isRequired
        ),
        items: arrayOf(shape({}))
    }),
    searchValue: string,
    setVisible: func,
    visible: bool
};
