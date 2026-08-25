import { apiData, apiMessage, apiUrl, jsonHeaders, readApiJson, userAuthHeaders, xTokenHeader } from './apiConfig.js';
import { getStoredUser } from './authSession.js';
import { endpoints } from './endpoints.js';

function userFromAccountPayload(data) {
  const nested = apiData(data);
  return nested?.user ?? data?.user ?? null;
}

/** Public auth: x-token only; include user headers when an admin is logged in. */
function registrationHeaders() {
  const u = getStoredUser();
  return userAuthHeaders(u) || { 'x-token': xTokenHeader() };
}

/** POST /v2/auth/register */
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
    const res = await fetch(apiUrl(endpoints.authRegister()), {
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

/** POST /v2/auth/user/verify-email */
export async function postVerifyAccountOtp({ email, otp }) {
  const mail = String(email ?? '').trim();
  if (!mail) return { ok: false, message: 'Email is required.' };

  try {
    const res = await fetch(apiUrl(endpoints.authVerifyEmail()), {
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

/** POST /v2/auth/user/forgot-password */
export async function postForgotPassword({ email }) {
  const mail = String(email ?? '').trim();
  if (!mail) return { ok: false, message: 'Email is required.' };

  try {
    const res = await fetch(apiUrl(endpoints.authForgotPassword()), {
      method: 'POST',
      headers: jsonHeaders({ 'x-token': xTokenHeader() }),
      credentials: 'include',
      body: JSON.stringify({ email: mail }),
    });
    let data = null;
    try {
      data = await readApiJson(res);
    } catch {
      return { ok: false, error: 'bad_json' };
    }

    if (data?.success) {
      return { ok: true, message: apiMessage(data, 'Password reset email sent.'), data };
    }

    return { ok: false, message: apiMessage(data, 'Could not request password reset.'), data };
  } catch {
    return { ok: false, error: 'network' };
  }
}

/** POST /v2/auth/user/reset-password */
export async function postResetPassword({ email, otp, password, confirm_password }) {
  try {
    const res = await fetch(apiUrl(endpoints.authResetPassword()), {
      method: 'POST',
      headers: jsonHeaders({ 'x-token': xTokenHeader() }),
      credentials: 'include',
      body: JSON.stringify({
        email: String(email ?? '').trim(),
        otp: String(otp ?? '').trim(),
        password: String(password ?? ''),
        confirm_password: String(confirm_password ?? ''),
      }),
    });
    let data = null;
    try {
      data = await readApiJson(res);
    } catch {
      return { ok: false, error: 'bad_json' };
    }

    if (data?.success) {
      return { ok: true, message: apiMessage(data, 'Password reset.'), data };
    }

    return { ok: false, message: apiMessage(data, 'Could not reset password.'), data };
  } catch {
    return { ok: false, error: 'network' };
  }
}
