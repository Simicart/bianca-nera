import { RestApi } from '@magento/peregrine';
import Identify from 'src/simi/Helper/Identify'

const { request } = RestApi.Magento2;

const isPasswordComplexEnough = (str = '') => {
	const count = {
		lower: 0,
		upper: 0,
		digit: 0,
		special: 0
	};

	for (const char of str) {
		if (/[a-z]/.test(char)) count.lower++;
		else if (/[A-Z]/.test(char)) count.upper++;
		else if (/\d/.test(char)) count.digit++;
		else if (/\S/.test(char)) count.special++;
	}

	return Object.values(count).filter(Boolean).length >= 3;
};

export const validators = new Map()
	.set('confirm', (value, values) => {
		return value !== values.password ? Identify.__('Passwords must match.') : undefined;
	})
	.set('email', (value) => {
		const trimmed = (value || '').trim();

		if (!trimmed) return Identify.__('An email address is required.');
		if (!trimmed.includes('@')) return Identify.__('A valid email address is required.');

		return undefined;
	})
	.set('firstName', (value) => {
		return !(value || '').trim() ? Identify.__('A first name is required.') : undefined;
	})
	.set('lastName', (value) => {
		return !(value || '').trim() ? Identify.__('A last name is required.') : undefined;
	})
	.set('vendorId', (value) => {
		return !(value || '').trim() ? Identify.__('Vendor ID is required.') : undefined;
	})
	.set('text', (value) => {
		return !(value || '').trim() ? Identify.__('This field is required.') : undefined;
	})
	.set('countryId', (value) => {
		return !(value || '').trim() ? Identify.__('Country is required.') : undefined;
	})
	.set('city', (value) => {
		return !(value || '').trim() ? Identify.__('City is required.') : undefined;
	})
	.set('region', (value) => {
		return !(value || '').trim() ? Identify.__('Region is required.') : undefined;
	})
	.set('telephone', (value) => {
		return !(value || '').trim() ? Identify.__('Phone number is required.') : undefined;
	})
	.set('website', (value) => {
		return !(value || '').trim() ? Identify.__('Website url is required.') : undefined;
	})
	.set('facebook', (value) => {
		return !(value || '').trim() ? Identify.__('Facebook link is required.') : undefined;
	})
	.set('instagram', (value) => {
		return !(value || '').trim() ? Identify.__('Instagram link is required.') : undefined;
	})
	.set('password', (value) => {
		if (!value || value.length < 8) {
			return Identify.__('A password must contain at least 8 characters.');
		}
		if (!isPasswordComplexEnough(value)) {
			return Identify.__('A password must contain at least 3 of the following: lowercase, uppercase, digits, special characters.');
		}

		return undefined;
	});

export const asyncValidators = new Map().set('email', async (value) => {
	try {
		const body = {
			customerEmail: value,
			website_id: null
		};

		// response is a boolean
		const available = await request('/rest/V1/customers/isEmailAvailable', {
			method: 'POST',
			body: JSON.stringify(body)
		});

		return !available ? Identify.__('This email address is not available.') : null;
	} catch (error) {
		throw Identify.__('An error occurred while looking up this email address.');
	}
});
