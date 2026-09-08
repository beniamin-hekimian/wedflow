import { Head, Link, router } from '@inertiajs/react';
import { ArrowLeft, CalendarDays, Eye, EyeOff, Users } from 'lucide-react';

import Navbar from '@/Components/Navbar';
import Pagination from '@/Components/Pagination';
import SortableTh from '@/Components/SortableTh';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { Card, CardContent } from '@/components/ui/card';
import { cn } from '@/lib/utils';

const formatDate = (value) => {
  if (!value) return '—';
  return new Date(value).toLocaleDateString(undefined, {
    year: 'numeric',
    month: 'short',
    day: 'numeric',
  });
};

const statCards = [
  { key: 'total', label: 'Total responses' },
  { key: 'attending', label: 'Attending' },
  { key: 'declined', label: 'Declined' },
  { key: 'confirmed_guests', label: 'Confirmed guests' },
  { key: 'wishes', label: 'Wishes' },
];

const columns = [
  { key: 'guest_name', label: 'Guest' },
  { key: 'is_attending', label: 'Attending' },
  { key: 'count', label: 'Guests' },
  { key: 'message', label: 'Wish message' },
  { key: 'is_hidden', label: 'Hidden' },
  { key: 'created_at', label: 'Submitted' },
];

export default function Responses({
  invitation,
  rows,
  summary = {},
  filters = {},
}) {
  const sort = filters.sort ?? 'created_at';
  const direction = filters.direction ?? 'desc';

  const handleSort = (key) => {
    const nextDirection = sort === key && direction === 'asc' ? 'desc' : 'asc';

    router.get(
      route('invitations.responses', invitation.slug),
      { sort: key, direction: nextDirection },
      { preserveState: true, preserveScroll: true },
    );
  };

  const toggleVisibility = (response) => {
    router.patch(
      route('invitations.responses.visibility', [invitation.slug, response.id]),
      {},
      { preserveScroll: true },
    );
  };

  return (
    <>
      <Head title={`Responses - ${invitation.groom_name} & ${invitation.bride_name}`} />

      <div className="flex min-h-screen flex-col bg-gray-50">
        <Navbar />

        <main className="flex-1">
          <div className="mx-auto max-w-7xl px-4 py-12 sm:px-6 lg:px-8">
            <div className="mb-6">
              <Link
                href={route('invitations.index')}
                className="inline-flex items-center gap-1 text-sm font-medium text-gray-500 transition hover:text-gray-900"
              >
                <ArrowLeft className="size-4" /> My Invitations
              </Link>
            </div>

            <div className="mb-4 flex items-center justify-between">
              <div>
                <h2 className="text-xl font-semibold leading-tight text-gray-800">
                  {invitation.groom_name} & {invitation.bride_name}
                </h2>
                <p className="mt-1 flex items-center gap-2 text-sm text-gray-500">
                  <CalendarDays className="size-4" />
                  {formatDate(invitation.event_date)}
                  {invitation.venue_name && (
                    <>
                      <span>·</span>
                      <span>{invitation.venue_name}</span>
                    </>
                  )}
                </p>
              </div>
              <Link href={`/${invitation.slug}`}>
                <Button variant="outline" size="sm">
                  View invitation
                </Button>
              </Link>
            </div>

            <div className="mb-6 grid grid-cols-2 gap-3 sm:grid-cols-3 lg:grid-cols-5">
              {statCards.map((stat) => (
                <Card key={stat.key}>
                  <CardContent className="p-4">
                    <p className="text-2xl font-semibold tabular-nums text-gray-900">
                      {summary[stat.key] ?? 0}
                    </p>
                    <p className="mt-0.5 text-xs font-medium uppercase tracking-wide text-gray-500">
                      {stat.label}
                    </p>
                  </CardContent>
                </Card>
              ))}
            </div>

            <div className="overflow-hidden rounded-xl bg-white shadow-sm ring-1 ring-gray-200">
              {rows.data.length === 0 ? (
                <Card>
                  <CardContent className="flex flex-col items-center px-6 py-16 text-center">
                    <Users className="size-8 text-gray-300" />
                    <p className="mt-4 text-sm font-medium text-gray-900">
                      No responses yet
                    </p>
                    <p className="mt-1 text-sm text-gray-500">
                      Responses from guests will appear here once they submit the RSVP form.
                    </p>
                  </CardContent>
                </Card>
              ) : (
                <>
                  <div className="overflow-x-auto">
                    <table className="min-w-full divide-y divide-gray-200">
                      <thead className="bg-gray-50">
                        <tr>
                          {columns.map((column) => (
                            <SortableTh
                              key={column.key}
                              column={column}
                              sort={sort}
                              direction={direction}
                              onSort={handleSort}
                            />
                          ))}
                        </tr>
                      </thead>
                      <tbody className="divide-y divide-gray-200 bg-white">
                        {rows.data.map((response) => (
                          <tr key={response.id} className="transition hover:bg-gray-50">
                            <td className="px-6 py-4 text-sm font-medium text-gray-900">
                              {response.guest_name}
                            </td>
                            <td className="px-6 py-4">
                              <Badge variant="outline" className="rounded-full">
                                <span
                                  className={cn(
                                    'size-2 shrink-0 rounded-full',
                                    response.is_attending
                                      ? 'bg-emerald-500'
                                      : 'bg-red-500',
                                  )}
                                />
                                {response.is_attending ? 'Accepted' : 'Declined'}
                              </Badge>
                            </td>
                            <td className="px-6 py-4 text-sm text-gray-600">
                              {response.is_attending ? response.count : '—'}
                            </td>
                            <td className="max-w-md px-6 py-4 text-sm text-gray-600">
                              {response.message ? (
                                <span className="line-clamp-2 whitespace-pre-line">
                                  {response.message}
                                </span>
                              ) : (
                                <span className="text-gray-400">—</span>
                              )}
                            </td>
                            <td className="px-6 py-4">
                              {response.message ? (
                                <div className="flex items-center gap-2">
                                  <Badge
                                    variant="outline"
                                    className={cn(
                                      'rounded-full',
                                      response.is_hidden && 'bg-gray-100 text-gray-500',
                                    )}
                                  >
                                    {response.is_hidden ? 'Hidden' : 'Visible'}
                                  </Badge>
                                  <Button
                                    variant="ghost"
                                    size="icon"
                                    className="size-7 text-gray-500 transition hover:text-gray-900"
                                    onClick={() => toggleVisibility(response)}
                                    aria-label={
                                      response.is_hidden
                                        ? 'Show wish to guests'
                                        : 'Hide wish from guests'
                                    }
                                  >
                                    {response.is_hidden ? (
                                      <EyeOff className="size-4" />
                                    ) : (
                                      <Eye className="size-4" />
                                    )}
                                  </Button>
                                </div>
                              ) : (
                                <span className="text-gray-400">—</span>
                              )}
                            </td>
                            <td className="px-6 py-4 text-sm text-gray-500">
                              {formatDate(response.created_at)}
                            </td>
                          </tr>
                        ))}
                      </tbody>
                    </table>
                  </div>

                  <Pagination pagination={rows} />
                </>
              )}
            </div>
          </div>
        </main>
      </div>
    </>
  );
}