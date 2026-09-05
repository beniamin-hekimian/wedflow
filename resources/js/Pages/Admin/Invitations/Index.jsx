import { Head, Link, router } from '@inertiajs/react';
import { Search, X } from 'lucide-react';
import { useEffect, useState } from 'react';

import Pagination from '@/Components/Pagination';
import AdminLayout from '@/Layouts/AdminLayout';
import { Button } from '@/components/ui/button';
import { Card, CardContent } from '@/components/ui/card';
import { Input } from '@/components/ui/input';
import {
  Select,
  SelectContent,
  SelectItem,
  SelectTrigger,
  SelectValue,
} from '@/components/ui/select';
import { cn } from '@/lib/utils';

const STATUSES = ['pending', 'active', 'inactive'];

const STATUS_ITEMS = {
  pending: 'Pending',
  active: 'Active',
  inactive: 'Inactive',
};

const statusDotClass = {
  pending: 'bg-yellow-500',
  active: 'bg-emerald-500',
  inactive: 'bg-red-500',
};

const formatDate = (value) => {
  if (!value) return '—';
  return new Date(value).toLocaleDateString(undefined, {
    year: 'numeric',
    month: 'short',
    day: 'numeric',
  });
};

export default function Index({ invitations, filters, statusCounts }) {
  const [search, setSearch] = useState(filters.search ?? '');
  const hasFilters = Boolean(filters.status) || Boolean(filters.search);

  useEffect(() => {
    const timeout = setTimeout(() => {
      if (search === filters.search) return;

      router.get(
        route('admin.invitations.index'),
        { ...(filters.status ? { status: filters.status } : {}), ...(search ? { search } : {}) },
        { preserveScroll: true, preserveState: true, only: ['invitations', 'filters'], replace: true },
      );
    }, 400);

    return () => clearTimeout(timeout);
  }, [search]);

  const tabHref = (status) => {
    const params = {};
    if (status) params.status = status;
    if (filters.search) params.search = filters.search;
    return route('admin.invitations.index', params);
  };

  const tabs = [
    { key: '', label: 'All', count: statusCounts?.total },
    { key: 'pending', label: 'Pending', count: statusCounts?.pending },
    { key: 'active', label: 'Active', count: statusCounts?.active },
    { key: 'inactive', label: 'Inactive', count: statusCounts?.inactive },
  ];

  const updateStatus = (invitation, status) => {
    router.patch(
      route('admin.invitations.status', invitation.id),
      { status },
      {
        preserveScroll: true,
        only: ['invitations', 'filters', 'statusCounts', 'flash'],
      },
    );
  };

  return (
    <>
      <Head title="Admin Invitations" />

      <AdminLayout>
        <div className="mb-6">
          <h1 className="text-2xl font-bold tracking-tight text-gray-900">
            Invitations
          </h1>
          <p className="mt-1 text-sm text-gray-500">
            Manage every invitation and its status.
          </p>
        </div>

        <div className="mb-4 flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
          <div className="flex flex-wrap items-center gap-2">
            {tabs.map((tab) => (
              <Link
                key={tab.key}
                href={tabHref(tab.key)}
                className={cn(
                  'inline-flex items-center gap-2 rounded-md px-3 py-1.5 text-sm font-medium transition',
                  (!filters.status && tab.key === '') || filters.status === tab.key
                    ? 'bg-gray-900 text-white'
                    : 'bg-white text-gray-600 ring-1 ring-gray-200 hover:bg-gray-50',
                )}
              >
                {tab.label}
                <span
                  className={cn(
                    'rounded-full px-2 py-0.5 text-xs tabular-nums',
                    (!filters.status && tab.key === '') || filters.status === tab.key
                      ? 'bg-white/20 text-white'
                      : 'bg-gray-100 text-gray-500',
                  )}
                >
                  {tab.count ?? 0}
                </span>
              </Link>
            ))}
          </div>

          <div className="flex items-center gap-2">
            {hasFilters && (
              <Link href={route('admin.invitations.index')}>
                <Button variant="ghost" size="sm">
                  <X /> Reset
                </Button>
              </Link>
            )}

            <div className="relative">
              <Search className="pointer-events-none absolute left-3 top-1/2 size-4 -translate-y-1/2 text-gray-400" />
              <Input
                type="search"
                value={search}
                onChange={(e) => setSearch(e.target.value)}
                placeholder="Search couple, venue, owner..."
                className="w-64 pl-9"
              />
            </div>
          </div>
        </div>

        <div className="overflow-hidden rounded-xl bg-white shadow-sm ring-1 ring-gray-200">
          {invitations.data.length === 0 ? (
            <Card>
              <CardContent className="py-16 text-center">
                <p className="text-sm font-medium text-gray-900">
                  No invitations found
                </p>
                <p className="mt-1 text-sm text-gray-500">
                  Try adjusting your filters or search.
                </p>
              </CardContent>
            </Card>
          ) : (
            <>
              <div className="overflow-x-auto">
                <table className="min-w-full divide-y divide-gray-200">
                  <thead className="bg-gray-50">
                    <tr>
                      <th className="px-6 py-3 text-left text-xs font-medium tracking-wider text-gray-500 uppercase">
                        Couple
                      </th>
                      <th className="px-6 py-3 text-left text-xs font-medium tracking-wider text-gray-500 uppercase">
                        Owner
                      </th>
                      <th className="px-6 py-3 text-left text-xs font-medium tracking-wider text-gray-500 uppercase">
                        Template
                      </th>
                      <th className="px-6 py-3 text-left text-xs font-medium tracking-wider text-gray-500 uppercase">
                        Event
                      </th>
                      <th className="px-6 py-3 text-left text-xs font-medium tracking-wider text-gray-500 uppercase">
                        Status
                      </th>
                      <th className="px-6 py-3 text-left text-xs font-medium tracking-wider text-gray-500 uppercase">
                        Created
                      </th>
                    </tr>
                  </thead>
                  <tbody className="divide-y divide-gray-200 bg-white">
                    {invitations.data.map((invitation) => (
                      <tr key={invitation.id} className="transition hover:bg-gray-50">
                        <td className="px-6 py-4">
                          <p className="text-sm font-medium text-gray-900">
                            {invitation.groom_name} & {invitation.bride_name}
                          </p>
                          <p className="text-xs text-gray-500">
                            #{invitation.id} · {invitation.venue_name}
                          </p>
                        </td>
                        <td className="px-6 py-4">
                          <p className="text-sm text-gray-900">
                            {invitation.user?.name}
                          </p>
                          <p className="text-xs text-gray-500">
                            {invitation.user?.email}
                          </p>
                        </td>
                        <td className="px-6 py-4 text-sm text-gray-600">
                          {invitation.template?.name ?? '—'}
                        </td>
                        <td className="px-6 py-4 text-sm text-gray-600">
                          {formatDate(invitation.event_date)}
                        </td>
                        <td className="px-6 py-4">
                          <Select
                            items={STATUS_ITEMS}
                            value={invitation.status}
                            onValueChange={(value) =>
                              updateStatus(invitation, value)
                            }
                          >
                            <SelectTrigger className="h-7 w-28 rounded-full px-2.5 text-xs font-medium [&_svg]:text-current">
                              <span
                                className={cn(
                                  'size-2 shrink-0 rounded-full',
                                  statusDotClass[invitation.status],
                                )}
                              />
                              <SelectValue />
                            </SelectTrigger>
                            <SelectContent>
                              {STATUSES.map((status) => (
                                <SelectItem key={status} value={status}>
                                  {STATUS_ITEMS[status]}
                                </SelectItem>
                              ))}
                            </SelectContent>
                          </Select>
                        </td>
                        <td className="px-6 py-4 text-sm text-gray-500">
                          {formatDate(invitation.created_at)}
                        </td>
                      </tr>
                    ))}
                  </tbody>
                </table>
              </div>

              <Pagination pagination={invitations} />
            </>
          )}
        </div>
      </AdminLayout>
    </>
  );
}