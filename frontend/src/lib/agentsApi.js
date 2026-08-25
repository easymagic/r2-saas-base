import { fetchUserByIdFromApi, fetchUsersFromApi, updateUserOnApi } from './userApi.js';

/**
 * No dedicated agents routes in Postman.
 * List agents via GET /v2/auth/users?search=agent; activate/deactivate via POST /v2/auth/user/{id}.
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

function isAgentUser(u) {
  return String(u?.role || '')
    .toLowerCase()
    .split(/[\s,|]+/)
    .includes('agent');
}

export async function fetchAgentsFromApi(user) {
  const r = await fetchUsersFromApi(user, 1, { search: 'agent' });
  if (!r.ok) return r;
  const agents = r.users.filter(isAgentUser);
  return { ok: true, agents, total: agents.length, data: r.data };
}

export async function fetchInactiveAgentsFromApi(user) {
  const r = await fetchAgentsFromApi(user);
  if (!r.ok) return r;
  const agents = r.agents.filter((a) => String(a?.status || '').toLowerCase() === 'inactive');
  return { ok: true, agents, total: agents.length, data: r.data };
}
