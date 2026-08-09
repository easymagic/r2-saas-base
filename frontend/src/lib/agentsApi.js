import { fetchUserByIdFromApi, updateUserOnApi } from './userApi.js';

/**
 * Agent activate/deactivate/list are not separate v2 routes.
 * Activate/deactivate map to user status updates; list returns empty (use user id lookup).
 */
async function patchAgentStatus(user, agentId, status) {
  const id = agentId != null ? String(agentId).trim() : '';
  if (!id) return { ok: false, error: 'bad_id' };

  const existing = await fetchUserByIdFromApi(user, id);
  if (!existing.ok || !existing.user) {
    return { ok: false, message: existing.message || 'Could not load agent.', data: existing.data };
  }

  const u = existing.user;
  const r = await updateUserOnApi(user, id, {
    name: u.name ?? '',
    phone: u.phone ?? '',
    role: u.role || 'agent',
    status,
    delivery_address: u.delivery_address ?? '',
    social_security_number: u.social_security_number ?? '',
    country_code: u.country_code ?? '',
  });
  if (!r.ok) return r;
  return {
    ok: true,
    agent: r.user,
    message: status === 'active' ? 'Agent marked active.' : 'Agent marked inactive.',
    data: r.data,
  };
}

export async function activateAgentFromApi(user, agentId) {
  return patchAgentStatus(user, agentId, 'active');
}

export async function deactivateAgentFromApi(user, agentId) {
  return patchAgentStatus(user, agentId, 'inactive');
}

export async function fetchAgentsFromApi() {
  return {
    ok: true,
    agents: [],
    lookupOnly: true,
    data: { success: true, data: { agents: [] } },
  };
}

export async function fetchInactiveAgentsFromApi() {
  return {
    ok: true,
    agents: [],
    lookupOnly: true,
    data: { success: true, data: { agents: [] } },
  };
}
