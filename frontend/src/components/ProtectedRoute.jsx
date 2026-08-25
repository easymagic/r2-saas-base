import { Navigate, useLocation } from 'react-router-dom';
import { getStoredUser } from '../lib/authSession.js';

export function ProtectedRoute({ children, requireAdmin = false }) {
  const location = useLocation();
  const user = getStoredUser();

  if (!user) {
    return <Navigate to="/login" state={{ from: location }} replace />;
  }

  if (requireAdmin && String(user.role || '').toLowerCase() !== 'admin') {
    return <Navigate to="/orders" replace />;
  }

  return children;
}
