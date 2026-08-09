import { Navigate, Route, Routes } from 'react-router-dom';
import { AdminRoute } from './components/AdminRoute.jsx';
import { AppLayout } from './components/layout/AppLayout.jsx';
import { ProtectedRoute } from './components/ProtectedRoute.jsx';
import { AdminBatchesPage } from './pages/AdminBatchesPage.jsx';
import { AdminDashboardPage } from './pages/AdminDashboardPage.jsx';
import { AdminPlatformConfigPage } from './pages/AdminPlatformConfigPage.jsx';
import { AdminSettingsPage } from './pages/AdminSettingsPage.jsx';
import { AdminTopupsPage } from './pages/AdminTopupsPage.jsx';
import { AdminUserDetailPage } from './pages/AdminUserDetailPage.jsx';
import { AdminUsersPage } from './pages/AdminUsersPage.jsx';
import { CreateOrderPage } from './pages/CreateOrderPage.jsx';
import { DashboardPage } from './pages/DashboardPage.jsx';
import { HomePage } from './pages/HomePage.jsx';
import { LoginPage } from './pages/LoginPage.jsx';
import { MigrationsPage } from './pages/MigrationsPage.jsx';
import { RegisterPage } from './pages/RegisterPage.jsx';
import { VerifyOtpPage } from './pages/VerifyOtpPage.jsx';
import { OrderDetailPage } from './pages/OrderDetailPage.jsx';
import { NotificationsPage } from './pages/NotificationsPage.jsx';
import { OrdersPage } from './pages/OrdersPage.jsx';
import { ProfilePage } from './pages/ProfilePage.jsx';
import { WalletPage } from './pages/WalletPage.jsx';

export default function App() {
  return (
    <Routes>
      <Route path="/" element={<HomePage />} />
      <Route path="/login" element={<LoginPage />} />
      <Route path="/migrations" element={<MigrationsPage />} />
      <Route path="/register" element={<RegisterPage />} />
      <Route path="/register/verify-otp" element={<VerifyOtpPage />} />
      <Route
        element={
          <ProtectedRoute>
            <AppLayout />
          </ProtectedRoute>
        }
      >
        <Route path="dashboard" element={<DashboardPage />} />
        <Route path="profile" element={<ProfilePage />} />
        <Route path="create-order" element={<CreateOrderPage />} />
        <Route path="orders" element={<OrdersPage />} />
        <Route path="orders/:orderId" element={<OrderDetailPage />} />
        <Route path="notifications" element={<NotificationsPage />} />
        <Route path="wallet" element={<WalletPage />} />
        <Route
          path="admin"
          element={
            <AdminRoute>
              <AdminDashboardPage />
            </AdminRoute>
          }
        />
        <Route
          path="admin/orders"
          element={
            <AdminRoute>
              <Navigate to="/orders" replace />
            </AdminRoute>
          }
        />
        <Route
          path="admin/topups"
          element={
            <AdminRoute>
              <AdminTopupsPage />
            </AdminRoute>
          }
        />
        <Route
          path="admin/batches"
          element={
            <AdminRoute>
              <AdminBatchesPage />
            </AdminRoute>
          }
        />
        <Route
          path="admin/users"
          element={
            <AdminRoute>
              <AdminUsersPage />
            </AdminRoute>
          }
        />
        <Route
          path="admin/users/:userId"
          element={
            <AdminRoute>
              <AdminUserDetailPage />
            </AdminRoute>
          }
        />
        <Route
          path="admin/platform-config"
          element={
            <AdminRoute>
              <AdminPlatformConfigPage />
            </AdminRoute>
          }
        />
        <Route
          path="admin/settings"
          element={
            <AdminRoute>
              <AdminSettingsPage />
            </AdminRoute>
          }
        />
      </Route>
      <Route path="*" element={<Navigate to="/" replace />} />
    </Routes>
  );
}
