import { Head, Link, usePage } from '@inertiajs/react';
import { ArrowRight, Activity, CheckCircle2, Clock, ShieldCheck } from 'lucide-react';

import AdminLayout from '@/Layouts/AdminLayout';
import { Button } from '@/components/ui/button';
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
    key: 'pending',
    label: 'Pending',
    icon: Clock,
  },
  {
    key: 'active',
    label: 'Active',
    icon: CheckCircle2,
  },
];

export default function Dashboard({ stats }) {
  const user = usePage().props.auth.user;

  return (
    <>
      <Head title="Admin Dashboard" />

      <AdminLayout>
        <div className="mb-6">
          <h1 className="flex items-center gap-2 text-2xl font-bold tracking-tight text-gray-900">
            <ShieldCheck className="size-6" />
            Welcome, {user?.name}
          </h1>
          <p className="mt-1 text-sm text-gray-500">
            You are signed in as an administrator.
          </p>
        </div>

        <div className="grid gap-4 sm:grid-cols-3">
          {statItems.map(({ key, label, icon: Icon }) => (
            <Card key={key}>
              <CardHeader className="flex flex-row items-center justify-between space-y-0 pb-2">
                <CardTitle className="text-sm font-medium text-gray-500">
                  {label}
                </CardTitle>
                <Icon className="size-4 text-gray-400" />
              </CardHeader>
              <CardContent>
                <p className="text-3xl font-bold text-gray-900">
                  {stats?.[key] ?? 0}
                </p>
              </CardContent>
            </Card>
          ))}
        </div>

        <Card className="mt-4">
          <CardHeader>
            <CardTitle>Invitations report</CardTitle>
            <CardDescription>
              View every invitation and manage its status.
            </CardDescription>
          </CardHeader>
          <CardContent>
            <Link href={route('admin.invitations.index')}>
              <Button>
                Open invitations report <ArrowRight />
              </Button>
            </Link>
          </CardContent>
        </Card>
      </AdminLayout>
    </>
  );
}