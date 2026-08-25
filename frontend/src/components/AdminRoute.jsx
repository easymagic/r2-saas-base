import { Navigate, useLocation } from 'react-router-dom';
import { getStoredUser } from '../lib/authSession.js';

/** Wraps admin-only pages inside the shared layout. */
export function AdminRoute({ children }) {
  const location = useLocation();
  const user = getStoredUser();

  if (!user) {
    return <Navigate to="/login" state={{ from: location }} replace />;
  }

  if (String(user.role || '').toLowerCase() !== 'admin') {
    return <Navigate to="/orders" replace />;
  }

  return children;
}
