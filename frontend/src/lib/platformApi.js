import { apiData, apiMessage, apiUrl, jsonHeaders, readApiJson, userAuthHeaders } from './apiConfig.js';
import { endpoints } from './endpoints.js';

function configsFromPayload(data) {
  const nested = apiData(data);
  if (Array.isArray(nested?.platform_configs)) return nested.platform_configs;
  if (Array.isArray(data?.platform_configs)) return data.platform_configs;
  return [];
}

function configsToMap(list) {
  const map = {};
  for (const row of list) {
    const key = row?.setting_key ?? row?.setting_name ?? row?.key;
    if (key == null || key === '') continue;
    map[String(key)] = row?.setting_value ?? row?.value ?? '';
  }
  return map;
}

/**
 * GET /v2/platform-configs — returns list + convenience map/config object for known keys.
 */
export async function fetchPlatformConfig(user) {
  const headers = userAuthHeaders(user);
  if (!headers) return { ok: false, error: 'no_session' };

  try {
    const res = await fetch(apiUrl(endpoints.platformConfigs()), {
      method: 'GET',
      headers,
      credentials: 'include',
    });
    let data = null;
    try {
      data = await readApiJson(res);
    } catch {
      return { ok: false, error: 'bad_json' };
    }

    if (!data?.success) {
      return { ok: false, message: apiMessage(data, 'Could not load platform configuration.'), data };
    }

    const list = configsFromPayload(data);
    const values = configsToMap(list);
    const config = {
      service_charge: values.SERVICE_CHARGE ?? values.service_charge ?? '',
      dollar_to_naira_rate: values.DOLLAR_TO_NAIRA_RATE ?? values.dollar_to_naira_rate ?? '',
      shipping_cost: values.SHIPPING_COST ?? values.shipping_cost ?? '',
      bank_name: values.BANK_NAME ?? values.bank_name ?? '',
      account_number: values.ACCOUNT_NUMBER ?? values.account_number ?? '',
      account_name: values.ACCOUNT_NAME ?? values.account_name ?? '',
      account_type: values.ACCOUNT_TYPE ?? values.account_type ?? '',
      ...values,
    };

    return { ok: true, config, list, values, data };
  } catch {
    return { ok: false, error: 'network' };
  }
}

/** POST /v2/platform-configs/update — JSON: setting_name, setting_value */
export async function updatePlatformConfigSetting(user, settingName, settingValue) {
  const headers = userAuthHeaders(user);
  if (!headers) return { ok: false, error: 'no_session' };

  try {
    const res = await fetch(apiUrl(endpoints.platformConfigsUpdate()), {
      method: 'POST',
      headers: jsonHeaders(headers),
      credentials: 'include',
      body: JSON.stringify({
        setting_name: String(settingName ?? '').trim(),
        setting_value: String(settingValue ?? '').trim(),
      }),
    });
    let data = null;
    try {
      data = await readApiJson(res);
    } catch {
      return { ok: false, error: 'bad_json' };
    }

    if (data?.success) {
      return { ok: true, data };
    }

    return { ok: false, message: apiMessage(data, 'Could not save setting.'), data };
  } catch {
    return { ok: false, error: 'network' };
  }
}

/**
 * Save common platform fields by updating each setting key.
 */
export async function savePlatformConfig(user, fields) {
  const pairs = [
    ['SERVICE_CHARGE', fields.service_charge],
    ['DOLLAR_TO_NAIRA_RATE', fields.dollar_to_naira_rate],
    ['SHIPPING_COST', fields.shipping_cost],
    ['BANK_NAME', fields.bank_name],
    ['ACCOUNT_NUMBER', fields.account_number],
    ['ACCOUNT_NAME', fields.account_name],
    ['ACCOUNT_TYPE', fields.account_type],
  ];

  for (const [name, value] of pairs) {
    if (value == null) continue;
    const r = await updatePlatformConfigSetting(user, name, value);
    if (!r.ok) return r;
  }

  const refreshed = await fetchPlatformConfig(user);
  if (refreshed.ok) {
    return { ok: true, config: refreshed.config, data: refreshed.data };
  }
  return { ok: true, config: fields };
}

/** DELETE /v2/platform-configs/{id}/delete */
export async function deletePlatformConfig(user, platformConfigId) {
  const headers = userAuthHeaders(user);
  if (!headers) return { ok: false, error: 'no_session' };
  const id = String(platformConfigId ?? '').trim();
  if (!id) return { ok: false, error: 'invalid_id' };

  try {
    const res = await fetch(apiUrl(endpoints.platformConfigDelete(id)), {
      method: 'DELETE',
      headers,
      credentials: 'include',
    });
    let data = null;
    try {
      data = await readApiJson(res);
    } catch {
      return { ok: false, error: 'bad_json' };
    }

    if (data?.success) {
      return { ok: true, data };
    }

    return { ok: false, message: apiMessage(data, 'Could not delete setting.'), data };
  } catch {
    return { ok: false, error: 'network' };
  }
}
