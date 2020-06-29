// export const isEmptyCartVisible = ({ cart, checkout: { step } }) =>
//     step === 'cart' && (!cart.details.items || cart.details.items.length === 0);

export const isEmptyCartVisible = ({ cart, checkout: { step } }) =>
    step === 'cart' && (!cart.totals.items || cart.totals.items.length === 0);

export const isMiniCartMaskOpen = ({ checkout: { step } }) => step === 'form';
