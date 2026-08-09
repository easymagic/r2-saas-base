import { Link } from 'react-router-dom';
import { UserHeader } from '../components/layout/UserHeader.jsx';
import { Badge } from '../components/ui/Badge.jsx';

export function AdminOrdersPage() {
  return (
    <>
      <UserHeader title="Orders" subtitle="Fulfillment queue · assign batches" />
      <main className="flex-1 p-4 sm:p-6 lg:p-8">
        <section className="rounded-2xl bg-white p-6 shadow-md">
          <div className="overflow-x-auto">
            <table className="w-full min-w-[44rem] border-collapse text-left text-sm">
              <thead>
                <tr className="border-b border-gray-200 bg-gray-100 text-xs font-semibold uppercase tracking-wide text-gray-600">
                  <th className="px-4 py-3" scope="col">
                    Order
                  </th>
                  <th className="px-4 py-3" scope="col">
                    Customer
                  </th>
                  <th className="px-4 py-3" scope="col">
                    Status
                  </th>
                  <th className="px-4 py-3" scope="col">
                    Batch
                  </th>
                  <th className="px-4 py-3 text-right" scope="col">
                    Amount
                  </th>
                  <th className="px-4 py-3 text-right" scope="col">
                    Action
                  </th>
                </tr>
              </thead>
              <tbody className="divide-y divide-gray-100">
                {[
                  { id: '1042', c: 'Jane D.', status: 'pending', batch: '—', amt: '₦43,000' },
                  { id: '1038', c: 'Acme', status: 'approved', batch: 'B-12', amt: '₦12,400' },
                ].map((r) => (
                  <tr key={r.id} className="hover:bg-gray-50">
                    <td className="px-4 py-3 font-medium text-blue-900">
                      <Link to={`/orders/${r.id}`}>#{r.id}</Link>
                    </td>
                    <td className="px-4 py-3 text-gray-700">{r.c}</td>
                    <td className="px-4 py-3">
                      <Badge variant={r.status === 'pending' ? 'pending' : 'approved'}>{r.status}</Badge>
                    </td>
                    <td className="px-4 py-3 text-gray-600">{r.batch}</td>
                    <td className="px-4 py-3 text-right tabular-nums">{r.amt}</td>
                    <td className="px-4 py-3 text-right">
                      <Link to="/admin/batches" className="text-sm font-medium text-orange-600 hover:text-orange-700">
                        Assign
                      </Link>
                    </td>
                  </tr>
                ))}
              </tbody>
            </table>
          </div>
        </section>
      </main>
    </>
  );
}
