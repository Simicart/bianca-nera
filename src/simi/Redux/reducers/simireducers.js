import { handleActions } from 'redux-actions';

import simiActions from 'src/simi/Redux/actions/simiactions';


const initialState = {
    simiValue: 'cody_initialize_value',
    simiMessages: [],// [{type: 'success', message: 'sample'}]
};

const reducerMap = {
    [simiActions.changeSampleValue]: (state, { payload }) => {
        return {
            ...state,
            simiValue: payload
        };
    },
    [simiActions.toggleMessages]: (state, { payload }) => {
        return {
            ...state,
            simiMessages: payload
        };
    },
};

export default handleActions(reducerMap, initialState);
