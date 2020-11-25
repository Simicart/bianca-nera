/**
 * See LICENSE.md for license details.
 */
/*browser:true*/
/*global define*/
define(
    [],
    function () {
        'use strict';

        return {
            getRules: function () {
                return {
                    'city': {
                        'required': true
                    },
                    'postcode': {
                        'required': true
                    },
                    'country_id': {
                        'required': true
                    }
                };
            }
        };
    }
)
