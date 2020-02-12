/**
 * @fileoverview This file houses functions that can be used for
 * validation of form fields.
 *
 * Note that these functions should return a string error message
 * when they fail, and `undefined` when they pass.
 */

import Identify from 'src/simi/Helper/Identify'

const SUCCESS = undefined;

export const hasLengthAtLeast = (value, values, minimumLength) => {
    if (!value || value.length < minimumLength) {
        return Identify.__(`Must contain at least ${minimumLength} character(s).`);
    }

    return SUCCESS;
};

export const hasLengthAtMost = (value, values, maximumLength) => {
    if (value && value.length > maximumLength) {
        return Identify.__(`Must not exceed ${maximumLength} character(s).`);
    }

    return SUCCESS;
};

export const hasLengthExactly = (value, values, length) => {
    if (value && value.length !== length) {
        return Identify.__(`Must contain exactly ${length} character(s).`);
    }

    return SUCCESS;
};

export const isRequired = value => {
    return (value || '').trim() ? SUCCESS : Identify.__('The field is required.');
};

export const validateEmail = value => {
    const regex = /^(([^<>()\[\]\\.,;:\s@"]+(\.[^<>()\[\]\\.,;:\s@"]+)*)|(".+"))@((\[[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}])|(([a-zA-Z\-0-9]+\.)+[a-zA-Z]{2,}))$/;

    return regex.test(value)
        ? SUCCESS
        : Identify.__('Please enter a valid email address (Ex: johndoe@domain.com).');
};

export const validateRegionCode = (value, values, countries) => {
    const country = countries.find(({ id }) => id === 'US');

    if (!country) {
        return Identify.__('Country "US" is not an available country.');
    }
    const { available_regions: regions } = country;

    if (!(Array.isArray(regions) && regions.length)) {
        return Identify.__('Country "US" does not contain any available regions.');
    }

    const region = regions.find(({ code }) => code === value);
    if (!region) {
        return Identify.__(`State "${value}" is not an valid state abbreviation.`);
    }

    return SUCCESS;
};
