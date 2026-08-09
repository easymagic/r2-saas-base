import { useCallback, useEffect, useState } from 'react';
import { getStoredUser, patchStoredUserWalletBalance, saveAuthUser } from '../lib/authSession.js';
import { fetchWalletBalanceFromApi } from '../lib/walletApi.js';
import { fetchMeFromApi } from '../lib/userApi.js';
import { formatNaira } from '../lib/userDisplay.js';

/** Shows cached balance, then refreshes from GET /api/me (full profile); falls back to GET /api/wallet. */
export function useSyncedWalletBalance() {
  const [label, setLabel] = useState(() => formatNaira(getStoredUser()?.wallet_balance));

  const refreshWalletBalance = useCallback(() => {
    const u = getStoredUser();
    if (!u?.token) return Promise.resolve();
    return fetchMeFromApi(u).then((me) => {
      if (me.ok && me.user) {
        saveAuthUser(me.user);
        setLabel(formatNaira(me.user.wallet_balance));
        return;
      }
      return fetchWalletBalanceFromApi(u).then((r) => {
        if (!r.ok) return;
        patchStoredUserWalletBalance(r.balance);
        setLabel(formatNaira(r.balance));
      });
    });
  }, []);

  useEffect(() => {
    refreshWalletBalance();
  }, [refreshWalletBalance]);

  return [label, refreshWalletBalance];
}
