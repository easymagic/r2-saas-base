import { fetchPlatformConfig, updatePlatformConfigSetting } from './platformApi.js';

const STORAGE_KEY = 'r2_app_settings';

function normalizeSettings(rawSettings) {
  if (!Array.isArray(rawSettings)) return { list: [], values: {} };

  const list = rawSettings
    .filter((setting) => setting && typeof setting === 'object')
    .map((setting) => {
      const key = setting.setting_key ?? setting.setting_name ?? setting.key;
      const value = setting.setting_value ?? setting.value;
      return {
        id: setting.id,
        key,
        value,
        setting_key: key,
        setting_value: value,
      };
    })
    .filter((setting) => typeof setting.key === 'string' && setting.key.length > 0);

  const values = list.reduce((acc, setting) => {
    acc[setting.key] = setting.value;
    return acc;
  }, {});

  return { list, values };
}

export function getStoredSettings() {
  try {
    const raw = localStorage.getItem(STORAGE_KEY);
    if (!raw) return { list: [], values: {} };
    return normalizeSettings(JSON.parse(raw)?.list);
  } catch {
    return { list: [], values: {} };
  }
}

export function saveSettings(rawSettings) {
  const settings = normalizeSettings(rawSettings);
  localStorage.setItem(STORAGE_KEY, JSON.stringify(settings));
  return settings;
}

export function clearSettings() {
  localStorage.removeItem(STORAGE_KEY);
}

/** Settings are backed by GET /v2/platform-configs. */
export async function fetchSettingsFromApi(user) {
  const r = await fetchPlatformConfig(user);
  if (!r.ok) {
    return { ok: false, error: r.error, message: r.message, data: r.data };
  }
  return { ok: true, settings: normalizeSettings(r.list), data: r.data };
}

export async function syncSettingsFromApi(user) {
  const stored = (() => {
    try {
      const raw = localStorage.getItem('r2_auth_user');
      return raw ? JSON.parse(raw) : null;
    } catch {
      return null;
    }
  })();
  const result = await fetchSettingsFromApi(user || stored);
  if (result.ok) {
    saveSettings(result.settings.list);
  }
  return result;
}

/** Save each setting via POST /v2/platform-configs/update. */
export async function saveSettingsOnApi(user, settingsValues) {
  const values = settingsValues && typeof settingsValues === 'object' ? settingsValues : {};
  for (const [key, value] of Object.entries(values)) {
    const r = await updatePlatformConfigSetting(user, key, value);
    if (!r.ok) return r;
  }
  const refreshed = await fetchSettingsFromApi(user);
  if (refreshed.ok) saveSettings(refreshed.settings.list);
  return { ok: true, data: refreshed.data };
}
