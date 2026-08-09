import { apiData, apiMessage, apiUrl, jsonHeaders, readApiJson, userAuthHeaders, xTokenHeader } from './apiConfig.js';
import { getStoredUser } from './authSession.js';

function userFromAccountPayload(data) {
  const nested = apiData(data);
  return nested?.user ?? data?.user ?? null;
}

/** Public auth: x-token only; include user headers when an admin is logged in. */
function registrationHeaders() {
  const u = getStoredUser();
  return userAuthHeaders(u) || { 'x-token': xTokenHeader() };
}

/**
 * POST /v2/auth/register — JSON body.
 * Fields: name, email, password, phone, delivery_address, social_security_number, country_code.
 */
export async function postRegisterAccount(fields) {
  const {
    name = '',
    email = '',
    password = '',
    phone = '',
    delivery_address = '',
    social_security_number = '',
    country_code = '',
  } = fields || {};

  try {
    const res = await fetch(apiUrl('/v2/auth/register'), {
      method: 'POST',
      headers: jsonHeaders(registrationHeaders()),
      credentials: 'include',
      body: JSON.stringify({
        name: String(name).trim(),
        email: String(email).trim(),
        password: String(password),
        phone: String(phone).trim(),
        delivery_address: String(delivery_address).trim(),
        social_security_number: String(social_security_number).trim(),
        country_code: String(country_code).trim(),
      }),
    });
    let data = null;
    try {
      data = await readApiJson(res);
    } catch {
      return { ok: false, error: 'bad_json' };
    }

    if (data?.success) {
      return { ok: true, user: userFromAccountPayload(data), data };
    }

    return { ok: false, message: apiMessage(data, 'Could not create account.'), data };
  } catch {
    return { ok: false, error: 'network' };
  }
}

/**
 * POST /v2/auth/user/verify-email — JSON: email, otp.
 */
export async function postVerifyAccountOtp({ email, otp }) {
  const mail = String(email ?? '').trim();
  if (!mail) return { ok: false, message: 'Email is required.' };

  try {
    const res = await fetch(apiUrl('/v2/auth/user/verify-email'), {
      method: 'POST',
      headers: jsonHeaders({ 'x-token': xTokenHeader() }),
      credentials: 'include',
      body: JSON.stringify({
        email: mail,
        otp: String(otp ?? '').trim(),
      }),
    });
    let data = null;
    try {
      data = await readApiJson(res);
    } catch {
      return { ok: false, error: 'bad_json' };
    }

    const user = userFromAccountPayload(data);
    if (data?.success && user) {
      return { ok: true, user, data };
    }

    return { ok: false, message: apiMessage(data, 'Could not verify code.'), data };
  } catch {
    return { ok: false, error: 'network' };
  }
}
