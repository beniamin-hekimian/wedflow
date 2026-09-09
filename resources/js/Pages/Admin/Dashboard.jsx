import { Head, usePage } from '@inertiajs/react';
import {
  Activity,
  CalendarDays,
  CheckCircle2,
  Clock,
  MessageSquareText,
  ShieldCheck,
  Users,
} from 'lucide-react';

import AdminLayout from '@/Layouts/AdminLayout';
import {
  Card,
  CardContent,
  CardDescription,
  CardHeader,
  CardTitle,
} from '@/components/ui/card';

const statItems = [
  {
    key: 'total',
    label: 'Total invitations',
    icon: Activity,
  },
  {
    key: 'active',
    label: 'Active',
    icon: CheckCircle2,
  },
  {
    key: 'pending',
    label: 'Pending',
    icon: Clock,
  },
  {
    key: 'users',
    label: 'Total users',
    icon: Users,
  },
  {
    key: 'admins',
    label: 'Admins',
    icon: ShieldCheck,
  },
];

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

export default function Dashboard({ stats, topInvitations }) {
  const user = usePage().props.auth.user;

  return (
    <>
      <Head title="Admin Dashboard" />

      <AdminLayout>
        <div className="mb-6">
          <h1 className="flex items-center gap-2 font-display text-2xl font-bold tracking-tight text-foreground">
            <ShieldCheck className="size-6" />
            Welcome, {user?.name}
          </h1>
          <p className="mt-1 text-sm text-muted-foreground">
            You are signed in as an administrator.
          </p>
        </div>

        <div className="grid gap-4 sm:grid-cols-2 lg:grid-cols-5">
          {statItems.map(({ key, label, icon: Icon }) => (
            <Card key={key}>
              <CardHeader className="flex flex-row items-center justify-between space-y-0 pb-2">
                <CardTitle className="text-sm font-medium text-muted-foreground">
                  {label}
                </CardTitle>
                <Icon className="size-4 text-muted-foreground" />
              </CardHeader>
              <CardContent>
                <p className="text-3xl font-bold text-foreground">
                  {stats?.[key] ?? 0}
                </p>
              </CardContent>
            </Card>
          ))}
        </div>

        <Card className="mt-4">
          <CardHeader>
            <CardTitle className="flex items-center gap-2">
              <MessageSquareText className="size-4 text-muted-foreground" />
              Top 3 invitations by RSVPs
            </CardTitle>
            <CardDescription>
              The invitations with the most received responses.
            </CardDescription>
          </CardHeader>
          <CardContent>
            {topInvitations.length === 0 ? (
              <p className="py-8 text-center text-sm text-muted-foreground">
                No RSVPs received yet.
              </p>
            ) : (
              <div className="divide-y divide-border">
                {topInvitations.map((invitation, index) => (
                  <div
                    key={invitation.id}
                    className="flex flex-col gap-3 py-4 first:pt-0 last:pb-0 sm:flex-row sm:items-center sm:justify-between"
                  >
                    <div className="flex min-w-0 items-center gap-3">
                      <span className="flex size-8 shrink-0 items-center justify-center rounded-full bg-primary/10 text-sm font-semibold text-primary">
                        {index + 1}
                      </span>
                      <div className="min-w-0">
                        <a
                          href={`/${invitation.slug}`}
                          target="_blank"
                          rel="noreferrer"
                          className="truncate text-sm font-medium text-foreground transition hover:text-primary"
                        >
                          {invitation.groom_name} & {invitation.bride_name}
                        </a>
                        <p className="mt-0.5 flex items-center gap-1 text-xs text-muted-foreground">
                          <CalendarDays className="size-3.5" />
                          {formatDate(invitation.event_date)}
                        </p>
                      </div>
                    </div>

                    <div className="flex flex-wrap items-center gap-2 sm:shrink-0">
                      <span className="inline-flex items-center gap-1.5 rounded-full bg-card px-2 py-0.5 text-xs font-medium text-muted-foreground ring-1 ring-border">
                        <span className={`size-1.5 rounded-full ${statusDotClass[invitation.status]}`} />
                        {STATUS_ITEMS[invitation.status]}
                      </span>
                      <span className="inline-flex items-center gap-1 rounded-full bg-muted px-2 py-0.5 text-xs font-medium tabular-nums text-foreground">
                        {invitation.responses_count}
                        <span className="font-normal text-muted-foreground">
                          RSVPs
                        </span>
                      </span>
                    </div>
                  </div>
                ))}
              </div>
            )}
          </CardContent>
        </Card>
      </AdminLayout>
    </>
  );
}